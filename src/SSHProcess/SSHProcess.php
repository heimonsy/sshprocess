<?php
namespace SSHProcess;

use Symfony\Component\Process\Process;

class SSHProcess extends Process
{
    use SSHProtocolTrait;

    public function __construct($hostname, $address, $username, $remoteCommand, $identityfile = null, $passphrase = null, $cwd = null, $port = 22, $timeout = 180)
    {
        $remoteCommand = addcslashes($remoteCommand, '"');

        if (!empty($passphrase)) {
            $cmd = "ssh -o ConnectTimeout=30 -i {$identityfile} {$username}@{$address} -p {$port} \"{$remoteCommand}\"";
            $commandline = $this->expectWithPassphrase($cmd, $passphrase, $timeout);
        } elseif (!empty($identityfile)) {
            $cmd = "ssh -o ConnectTimeout=30 -i {$identityfile} {$username}@{$address} -p {$port} \"{$remoteCommand}\"";
            $commandline = $this->expect($cmd, $timeout);
        } else {
            $cmd = "ssh -o ConnectTimeout=30 {$hostname} -p {$port} \"{$remoteCommand}\"";
            $commandline = $this->expect($cmd, $timeout);
        }

        parent::__construct($commandline, $cwd);
    }
}


