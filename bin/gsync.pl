#!/usr/bin/perl

use strict;
use warnings;

use File::Copy;

# config
my $repoDir = '/home/radio/rfk/';
my $workingDir = '/usr/share/rfk/';
my $gitwebURL = 'http://94.23.239.51/gitweb/?p=rfk.git;a=commit;h=';

chdir($repoDir);

my $gitRev = pop @ARGV or die "[-] Usage: $0 HASH\n";
my (@gitShowArray, @gitDiffArray, $gitHEAD);

open(GIT, "git show --name-only HEAD |") or die "[-] Can't open pipe\n";
	@gitShowArray = <GIT>;
close(GIT);

if($gitShowArray[0] =~ /commit ([a-f0-9]+)/){ $gitHEAD = $1; }
else { die "[-] No HEAD found\n"; }

open(GIT, "git diff --name-status HEAD $gitRev |") or die "[-] Can't open pipe\n";
	@gitDiffArray = <GIT>;
close(GIT);

for(@gitDiffArray) {
	
	# (M)odified, (A)dded
	if(/^(M|A)\t(.*)/i){
		copy($repoDir.$2, $workingDir.$2);
		print;
	}
	
}

# Write version-file
open(VERSION, '>', $workingDir.'var/version.php') or die "[-] Can't open file\n";
print VERSION "<?\n";
print VERSION "\$template['git']['rev'] = '$gitHEAD';\n";
print VERSION "\$template['git']['url'] = '${gitwebURL}${gitHEAD}';\n";
print VERSION "?>\n";
close(VERSION);
