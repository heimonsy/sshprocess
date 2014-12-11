<?php
namespace SSHProcess;

use Symfony\Component\Process\Process;

class SSHProcess extends Process
{
    use SSHProtocolTrait;

    public function __construct($hostname, $address, $username, $remoteCommand, $identityfile = null, $passphrase = null, $cwd = null)
    {
        if (!empty($passphrase)) {
            $cmd = "ssh -o ConnectTimeout=30 -i {$identityfile} {$username}@{$address} \"{$remoteCommand}\"";
            $commandline = $this->expectWithPassphrase($cmd, $passphrase);
        } elseif (!empty($identityfile)) {
            $cmd = "ssh -o ConnectTimeout=30 -i {$identityfile} {$username}@{$address} \"{$remoteCommand}\"";
            $commandline = $this->expect($cmd);
        } else {
            $cmd = "ssh -o ConnectTimeout=30 {$hostname} \"{$remoteCommand}\"";
            $commandline = $this->expect($cmd);
        }

        parent::__construct($commandline, $cwd);
    }
}


