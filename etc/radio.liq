
set("log.file.path","/home/teddydestodes/src/rfk//var/log/liquidsoap.log")
set("log.stdout", true)
set("server.telnet", true)
set("harbor.bind_addr","192.168.2.134")
def crossfade(a,b)
            add(normalize=false,
            [ sequence([ blank(duration=5.),
            fade.initial(duration=10.,b) ]),
            fade.final(duration=10.,a) ])
         end
def next(j,a,b)
          add(normalize=false,[ sequence(merge=true,[ blank(duration=3.),
              fade.initial(duration=6.,b) ]),
              sequence([fade.final(duration=9.,a),
              j,fallback([])]) ])
          end
def transition(j,a,b)
              add(normalize=false,[ fade.initial(b),sequence(merge=true,
              [blank(duration=1.),j,fallback([])]),
              fade.final(a) ])
          end
def onfade(old, new)
            add([amplify(2.0,new), amplify(0.1, old)])
          end

          def outfade(old, new)
            add([new, old])
          end
def auth(login,password) =
            ret = get_process_lines("php /home/teddydestodes/src/rfk//bin/liquidsoap.php auth #{quote(login)} #{quote(password)}")
            ret = list.hd(ret)
            bool_of_string(ret)
          end

          def live_start(mdata)
            ignore(system("php /home/teddydestodes/src/rfk//bin/liquidsoap.php connect #{quote(json_of(compact=true,mdata))}"))
          end

          def live_stop()
            ignore(test_process("php /home/teddydestodes/src/rfk//bin/liquidsoap.php disconnect"))
          end

          def writemeta(mdata)
            ignore(system("php /home/teddydestodes/src/rfk//bin/liquidsoap.php meta #{quote(json_of(compact=true,mdata))}"))
          end

live = input.harbor(port= 8080,on_connect = live_start, on_disconnect = live_stop, buffer=0., max = 10., auth = auth, "/live.ogg")

playlist = request.dynamic({ request.create("bar:foo", indicators= get_process_lines("php /home/teddydestodes/src/rfk//bin/playlist.php"))})
playlist = rewrite_metadata([("title","Kein Strömbernd")], playlist)
playlist = rewrite_metadata([("artist","Radio freies Krautchan")], playlist)
#playlist = mksave(playlist)

live = on_metadata(writemeta , live)

full = fallback(track_sensitive=false,transitions=[crossfade],[live,playlist])
MP3 =output.icecast(%mp3(stereo=true, samplerate=44100, bitrate=192),
	host="192.168.2.101",port=8000,protocol="http",
    user="source",password="hackme",
    mount="/radio.mp3",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    OGG =output.icecast(%vorbis(samplerate=44100, channels=2, quality=0.3),
	host="192.168.2.101",port=8000,protocol="http",
    user="source",password="hackme",
    mount="/radio.ogg",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    OGGHQ =output.icecast(%vorbis(samplerate=44100, channels=2, quality=0.6),
	host="192.168.2.101",port=8000,protocol="http",
    user="source",password="hackme",
    mount="/radiohq.ogg",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    AACP =output.icecast(%aacplus(channels=2, samplerate=44100, bitrate=28),
	host="192.168.2.101",port=8000,protocol="http",
    user="source",password="hackme",
    mount="/radio.aacp",
    url="radio.krautchan.net",public=false,
    fallible=true,restart=true,
    full)
    