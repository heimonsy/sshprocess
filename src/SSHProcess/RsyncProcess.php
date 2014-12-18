<?php
namespace SSHProcess;

use Symfony\Component\Process\Process;

class RsyncProcess extends Process
{
    use SSHProtocolTrait;

    const FORCE_DELETE = ' --force --delete ';
    const KEEP_FILES = ' ';

    public function __construct($hostname, $address, $username, $exclude, $localDir, $remoteDir, $forceDelete, $identityfile = null, $passphrase = null, $cwd = null, $port = 22, $timeout = 180)
    {
        $exclude = empty($exclude) ? '' : " --exclude-from={$exclude} ";

        if (!empty($passphrase)) {
            $cmd = "rsync -az {$forceDelete} --delay-updates -e \"ssh -o ConnectTimeout=30 -i {$identityfile} -p {$port}\"" .
                " {$exclude} {$localDir} {$username}@{$address}:$remoteDir ";
            $commandline = $this->expectWithPassphrase($cmd, $passphrase, $timeout);
        } elseif (!empty($identityfile)) {
            $cmd = "rsync -az {$forceDelete} --delay-updates -e \"ssh -o ConnectTimeout=30 -i {$identityfile} -p {$port}\"" .
                " {$exclude} {$localDir} {$username}@{$address}:$remoteDir ";
            $commandline = $this->expect($cmd, $timeout);
        } else {
            $cmd = "rsync -az {$forceDelete} --delay-updates -e \"ssh -o ConnectTimeout=30\" " .
                " {$exclude} {$localDir} {$hostname}:$remoteDir ";
            $commandline = $this->expect($cmd, $timeout);
        }

        parent::__construct($commandline, $cwd);
    }
}
