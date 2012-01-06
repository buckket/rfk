
set("log.file.path","/home/teddydestodes/src/rfk/var/log/liquidsoap.log")
set("log.stdout", true)
set("server.telnet", true)
set("harbor.bind_addr","192.168.2.2")
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
            ret = get_process_lines("php /home/teddydestodes/src/rfk/bin/liquidsoap.php auth #{quote(login)} #{quote(password)}")
            ret = list.hd(ret)
            bool_of_string(ret)
          end

          def live_start(mdata)
            ignore(system("php /home/teddydestodes/src/rfk/bin/liquidsoap.php connect #{quote(json_of(compact=true,mdata))}"))
          end

          def live_stop()
            ignore(test_process("php /home/teddydestodes/src/rfk/bin/liquidsoap.php disconnect"))
          end

          def writemeta(mdata)
            ignore(system("php /home/teddydestodes/src/rfk/bin/liquidsoap.php meta #{quote(json_of(compact=true,mdata))}"))
          end

live = input.harbor(port= 8010,on_connect = live_start, on_disconnect = live_stop, buffer=0., max = 10., auth = auth, "/live.ogg")

playlist = request.dynamic({ request.create("bar:foo", indicators= get_process_lines("php /home/teddydestodes/src/rfk/bin/playlist.php"))})
playlist = rewrite_metadata([("title","Kein Strömbernd")], playlist)
playlist = rewrite_metadata([("artist","Radio freies Krautchan")], playlist)
#playlist = mksave(playlist)

live = on_metadata(writemeta , live)
live = lastfm.submit.full(user="username", password="password", delay=0., force=true, live)

full = fallback(track_sensitive=false,transitions=[crossfade],[live,playlist])

def record_close(filename)
    ignore(system("php /home/teddydestodes/src/rfk/bin/liquidsoap.php record finish #{quote(filename)}"))
end

recordstream = output.file(%mp3.vbr(stereo=true, samplerate=44100, quality=4),
                           "/tmp/stream.tmp.mp3",live,
                           fallible = true, append = true, start = false, id="recordstream", on_close = record_close
)
MP3 =output.icecast(%mp3.vbr(stereo=true, samplerate=44100, quality=5,id3v2=true),
	host="192.168.2.2",port=8000,protocol="http",
    user="source",password="krautkanal",
    mount="/radio.mp3",
    url="radio.krautchan.net",public=false,
    description="RfK MP3",
    fallible=true,
    full)
    OGG =output.icecast(%vorbis(samplerate=44100, channels=2, quality=0.3),
	host="192.168.2.2",port=8000,protocol="http",
    user="source",password="krautkanal",
    mount="/radio.ogg",
    url="radio.krautchan.net",public=false,
    description="RfK OGG",
    fallible=true,
    full)
    OGGHQ =output.icecast(%vorbis(samplerate=44100, channels=2, quality=0.6),
	host="192.168.2.2",port=8000,protocol="http",
    user="source",password="krautkanal",
    mount="/radiohq.ogg",
    url="radio.krautchan.net",public=false,
    description="RfK OGG HQ",
    fallible=true,
    full)
    AACP =output.icecast(%aacplus(channels=2, samplerate=44100, bitrate=28),
	host="192.168.2.2",port=8000,protocol="http",
    user="source",password="krautkanal",
    mount="/radio.aacp",
    url="radio.krautchan.net",public=false,
    description="RfK AACP",
    fallible=true,
    full)
    