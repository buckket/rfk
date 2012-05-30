#!/usr/bin/perl

use RRDTool::OO;
use strict;
use warnings;

chdir "/home/radio/radio/var/lib/rrd";
my $rrd = RRDTool::OO->new(file => "1.rrd" );

$rrd->graph(
	image => "/home/radio/radio/www/graphh.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'Hörerzahlen (Stunde)',
	
	start          => time() - 3600,
	end            => time(),
    
	draw => {
		file => "1.rrd",
		name => "l1",
		legend => "RfK MP3",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'LAST',
	},
	draw => {
		file => "2.rrd",
		name => "l2",
		legend => "RfK OGG",
		stack => 1,
		type => "area",
		color => "0099FF",
		cfunc => 'LAST',
		
	},
	draw => {
		file => "4.rrd",
		name => "l3",
		legend => "RfK OGG HQ",
		stack => 1,
		type => "area",
		color => "0085FF",
		cfunc => 'LAST',
		
	},
        draw => {
                file => "5.rrd",
                name => "l4",
                legend => "RfK AACP",
                stack => 1,
                type => "area",
                color => "99FF00",
                cfunc => 'LAST',

        },
);

$rrd->graph(
	image => "/home/radio/radio/www/graphd.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'Hörerzahlen (Tag)',

	draw => {
		file => "1.rrd",
		name => "l1",
		legend => "RfK MP3",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'LAST',
	},
	draw => {
		file => "2.rrd",
		name => "l2",
		legend => "RfK OGG",
		stack => 1,
		type => "area",
		color => "0099FF",
		cfunc => 'LAST',
		
	},
	draw => {
		file => "4.rrd",
		name => "l3",
		legend => "RfK OGG HQ",
		stack => 1,
		type => "area",
		color => "0085FF",
		cfunc => 'LAST',
		
	},
        draw => {
                file => "5.rrd",
                name => "l4",
                legend => "RfK AACP",
                stack => 1,
                type => "area",
                color => "99FF00",
                cfunc => 'LAST',

        },
);

$rrd->graph(
	image => "/home/radio/radio/www/graphw.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'Hörerzahlen (Woche)',
	start          => time() - 3600*24*7,
	end            => time(),

	draw => {
		file => "1.rrd",
		name => "l1",
		legend => "RfK MP3",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'LAST',
	},
	draw => {
		file => "2.rrd",
		name => "l2",
		legend => "RfK OGG",
		stack => 1,
		type => "area",
		color => "0099FF",
		cfunc => 'LAST',
		
	},
	draw => {
		file => "4.rrd",
		name => "l3",
		legend => "RfK OGG HQ",
		stack => 1,
		type => "area",
		color => "0085FF",
		cfunc => 'LAST',
		
	},
        draw => {
                file => "5.rrd",
                name => "l4",
                legend => "RfK AACP",
                stack => 1,
                type => "area",
                color => "99FF00",
                cfunc => 'LAST',

        },
);

$rrd->graph(
	image => "/home/radio/radio/www/irc.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'IRC-Benutzer (Tag)',

	draw => {
		file => "irc.rrd",
		name => "users",
		legend => "Benutzer",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'AVERAGE',
	}
);

$rrd->graph(
	image => "/home/radio/radio/www/ircw.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'IRC-Benutzer (Woche)',
	start          => time() - 3600*24*7,
	end            => time(),

	draw => {
		file => "irc.rrd",
		name => "users",
		legend => "Benutzer",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'AVERAGE',
	}
);

$rrd->graph(
	image => "/home/radio/radio/www/ircl.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'IRC LPH (Tag)',

	draw => {
		file => "/var/lib/rrd/rfk_lines_per_hour.rrd",
		name => "lines",
		legend => "Lines",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'AVERAGE',
	}
);

$rrd->graph(
	image => "/home/radio/radio/www/irclw.png",
	height => 120,
	width => 500,
	lower_limit => 0,
	title => 'IRC LPH (Woche)',
	start          => time() - 3600*24*7,
	end            => time(),

	draw => {
		file => "/var/lib/rrd/rfk_lines_per_hour.rrd",
		name => "lines",
		legend => "Lines",
		stack => 1,
		type => "area",	
		color => "FF9900",
		cfunc => 'AVERAGE',
	}
);

