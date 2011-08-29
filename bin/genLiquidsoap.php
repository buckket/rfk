<?php
require_once dirname(dirname(__FILE__)).'/lib/common.inc.php';


echo '
set("log.file.path","'.$_config['base'].'/var/log/liquidsoap.log")
set("log.stdout", true)
set("server.telnet", true)
set("harbor.bind_addr","'.$_config['liquidsoap_address'].'")
';
crossfade();
trans_next();
transition();
fade();
external();
sources();
output();


function crossfade(){
    echo 'def crossfade(a,b)
            add(normalize=false,
            [ sequence([ blank(duration=5.),
            fade.initial(duration=10.,b) ]),
            fade.final(duration=10.,a) ])
         end
';
}

function trans_next(){
    echo 'def next(j,a,b)
          add(normalize=false,[ sequence(merge=true,[ blank(duration=3.),
              fade.initial(duration=6.,b) ]),
              sequence([fade.final(duration=9.,a),
              j,fallback([])]) ])
          end
';
}

function transition() {
    echo 'def transition(j,a,b)
              add(normalize=false,[ fade.initial(b),sequence(merge=true,
              [blank(duration=1.),j,fallback([])]),
              fade.final(a) ])
          end
';
}

function fade() {
    echo 'def onfade(old, new)
            add([amplify(2.0,new), amplify(0.1, old)])
          end

          def outfade(old, new)
            add([new, old])
          end
';
}

function external() {
    global $_config;
    echo 'def auth(login,password) =
            ret = get_process_lines("php '.$_config['base'].'/bin/liquidsoap.php auth #{quote(login)} #{quote(password)}")
            ret = list.hd(ret)
            bool_of_string(ret)
          end

          def live_start(mdata)
            ignore(system("php '.$_config['base'].'/bin/liquidsoap.php connect #{quote(json_of(compact=true,mdata))}"))
          end

          def live_stop()
            ignore(test_process("php '.$_config['base'].'/bin/liquidsoap.php disconnect"))
          end

          def writemeta(mdata)
            ignore(system("php '.$_config['base'].'/bin/liquidsoap.php meta #{quote(json_of(compact=true,mdata))}"))
          end
';
}

function sources () {
    global $_config;
    echo '
live = input.harbor(port= '.$_config['liquidsoap_port'].',on_connect = live_start, on_disconnect = live_stop, buffer=0., max = 10., auth = auth, "/live.ogg")

playlist = request.dynamic({ request.create("bar:foo", indicators= get_process_lines("php '.$_config['base'].'/bin/playlist.php"))})
playlist = rewrite_metadata([("title","Kein Strömbernd")], playlist)
playlist = rewrite_metadata([("artist","Radio freies Krautchan")], playlist)
#playlist = mksave(playlist)

live = on_metadata(writemeta , live)

full = fallback(track_sensitive=false,transitions=[crossfade],[live,playlist])
';
}

function output(){
    global $db;
    $sql = "SELECT *
            FROM mounts";
    $res = $db->query($sql);
    while($row = $db->fetch($res)) {
        //print_r($row);
        switch($row['type']){
            case 'LAME':
                makeLame($row);
                break;
            case 'OGG':
                makeOGG($row);
                break;
            case 'AACP':
                makeAAC($row);
                break;
        }
    }
}

function makeLame($array) {
    global $_config;
    echo $array['name'].' =output.icecast(%mp3(stereo=true, samplerate=44100, bitrate='.$array['quality'].'),
	host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
    user="'.$array['username'].'",password="'.$array['password'].'",
    mount="'.$array['path'].'",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    ';
}
function makeOGG($array) {
    global $_config;
    echo $array['name'].' =output.icecast(%vorbis(samplerate=44100, channels=2, quality=0.'.$array['quality'].'),
	host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
    user="'.$array['username'].'",password="'.$array['password'].'",
    mount="'.$array['path'].'",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    ';
}
function makeAAC($array) {
    global $_config;
    echo $array['name'].' =output.icecast(%aacplus(channels=2, samplerate=44100, bitrate='.$array['quality'].'),
	host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
    user="'.$array['username'].'",password="'.$array['password'].'",
    mount="'.$array['path'].'",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    ';
}
?>