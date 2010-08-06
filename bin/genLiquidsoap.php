<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';


echo 'set("log.file.path","'.$basePath.'/var/log/liquidsoap.log")
      set("log.stdout", false)
      set("server.telnet", true)
      set("harbor.bind_addr","'.$_config['liquidsoap_address'].'")
      set("harbor.port",'.$_config['liquidsoap_port'].')

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
    global $basePath;
    echo 'def depair(data)
            "#{fst(data)}=\'#{snd(data)}\'"
          end

    def auth(login,password) =
            ret = get_process_lines("php '.$basePath.'/bin/liquidsoap.php auth #{quote(login)} #{quote(password)}")
            ret = list.hd(ret)
            bool_of_string(ret)
          end

          def live_start(mdata)
            data = string.concat(separator=";", list.map(depair, mdata))
            ignore(test_process("php '.$basePath.'/bin/liquidsoap.php connect #{quote(data)}"))
          end

          def live_stop()
            ignore(test_process("php '.$basePath.'/bin/liquidsoap.php disconnect"))
          end

          def writemeta(mdata)
            mymeta = string.concat(separator=";", list.map(depair, mdata))
            ignore(test_process("php '.$basePath.'/bin/liquidsoap.php meta #{quote(mymeta)}"))
          end
';
}

function sources () {
    echo 'live = input.harbor(on_connect = live_start, on_disconnect = live_stop, buffer=0., max = 10., auth = auth, "/live.ogg")

          playlist = playlist(reload=10,"http://localhost/rfk/api/playlist.php")
          playlist = rewrite_metadata([("title","Kein Stroembernd")], playlist)
          playlist = rewrite_metadata([("artist","Radio freies Krautchan")], playlist)

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
        }
    }
}

function makeLame($array) {
    global $_config;
    echo $array['name'].' = output.icecast.lame(
            restart=true,
            host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
            user="'.$array['username'].'",password="'.$array['password'].'",
            mount="'.$array['path'].'",bitrate='.$array['quality'].',
            url="radio.krautchan.net",public=false,
            restart_on_crash=true,
            fallible=true,
            full)
            ';
}
function makeOGG($array) {
    global $_config;
    echo $array['name'].' = output.icecast.vorbis(
            restart=true,
            host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
            user="'.$array['username'].'",password="'.$array['password'].'",
            mount="'.$array['path'].'",quality='.$array['quality'].'.0,
            url="radio.krautchan.net",public=false,
            fallible=true,
            full)
            ';
}
function makeAAC($array) {
    global $_config;
    echo $array['name'].' = output.icecast.aacplus(
            restart=true,
            host="'.$_config['icecast_address'].'",port='.$_config['icecast_port'].',protocol="http",
            user="'.$array['username'].'",password="'.$array['password'].'",
            mount="'.$array['path'].'",quality='.$array['quality'].'.0,
            url="radio.krautchan.net",public=false,
            restart_on_crash=true,
            fallible=true,
            full
            ';
}
?>