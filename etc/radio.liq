#!/usr/bin/liquidsoap
set("log.file.path","/home/radio/var/log/liquidsoap.log")
set("log.stdout", false)
set("server.telnet", true)

#harbor settings
set("harbor.bind_addr","88.80.19.136")
set("harbor.port",8080)

# A simple (long) cross-fade
def crossfade(a,b)
  add(normalize=false,
	  [ sequence([ blank(duration=5.),
	               fade.initial(duration=10.,b) ]),
	    fade.final(duration=10.,a) ])
end

# Partially apply next to give it a jingle source.
# It will fade out the old source, then play the jingle.
# At the same time it fades in the new source.
def next(j,a,b)
  add(normalize=false,
	  [ sequence(merge=true,
	             [ blank(duration=3.),
	               fade.initial(duration=6.,b) ]),
	    sequence([fade.final(duration=9.,a),
	              j,fallback([])]) ])
end

# A similar transition,
# which does a cross-fading from A to B
# and adds a jingle
def transition(j,a,b)
  add(normalize=false,
	  [ fade.initial(b),
	    sequence(merge=true,
	            [blank(duration=1.),j,fallback([])]),
	    fade.final(a) ])
end

def depair(data)
  "#{fst(data)}='#{snd(data)}'"
end

def auth(login,password) = 
  ret = get_process_lines('php ~/bin/liquidsoap.php auth #{quote(login)} #{quote(password)}')
  ret = list.hd(ret)
  bool_of_string(ret)
end

def live_start(mdata)
  data = string.concat(separator=";", list.map(depair, mdata))
  ignore(test_process("php ~/bin/liquidsoap.php connect #{quote(data)}"))
end

def live_stop()
  ignore(test_process("php ~/bin/liquidsoap.php disconnect"))
end

def writemeta(mdata)
  mymeta = string.concat(separator=";", list.map(depair, mdata))
# system("echo \"#{mymeta}\" > ~/meta.txt")
  ignore(test_process('php ~/bin/liquidsoap.php meta #{quote(mymeta)}'))
end

#def mymeta(meta)
#	ignore(test_process('php ~/bin/liquidsoap.php meta #{quote(string_of(meta))}'))
#end

def onfade(old, new)
	add([amplify(2.0,new), amplify(0.1, old)])
end

def outfade(old, new)
	add([new, old])
end

text = request.queue(id="bot")
live = input.harbor(on_connect = live_start, on_disconnect = live_stop, buffer=0., max = 10., auth = auth, "/live.ogg")

loop = single("/home/radio/var/music/loop.ogg")
loop = rewrite_metadata([("title","Kein Strömbernd")], loop)
loop = rewrite_metadata([("artist","Radio freies Krautchan")], loop)

live = on_metadata(writemeta , live)
default = fallback( track_sensitive = false, transitions = [onfade, outfade], [text, loop] )
full = fallback(track_sensitive=false,transitions=[crossfade],[live,default])


output.icecast(
 mount = "radio.mp3",
 fallible=true,start=true,restart=true,restart_delay=3,
 name= "Radio freies Krautchan",
 protocol="http",
 user = "source",
 password = "krautkanal",
 port = 8000,
 host = "88.80.19.136",
 public=false,
 description="Radio freies Krautchan",
 %mp3(bitrate=192),
 full)


output.icecast(
 mount = "radio.ogg",
 fallible=true,start=true,restart=true,restart_delay=3,
 name= "Radio freies Krautchan",
 protocol="http",
 user = "source",
 password = "krautkanal",
 port = 8000,
 host = "88.80.19.136",
 public=false,
 description="Radio freies Krautchan",
 %vorbis(quality=0.3),
 full)

output.icecast(
 mount = "radiohq.ogg",
 fallible=true,start=true,restart=true,restart_delay=3,
 name= "Radio freies Krautchan",
 protocol="http",
 user = "source",
 password = "krautkanal",
 port = 8000,
 host = "88.80.19.136",
 public=false,
 description="Radio freies Krautchan",
 %vorbis(quality=0.6),
 full)
 
def gskip(_) = source.skip(text) "Done" end

server.register( namespace = "",
                 usage = "gskip", description = "gskip",
                 "gskip", gskip )

