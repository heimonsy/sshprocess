<?php
namespace SSHProcess;

use Symfony\Component\Process\Process;

trait SSHProtocolTrait
{
    protected function expect($command)
    {
        return  <<<EOD
expect << EOF
set timeout 180
spawn {$command}
while (1) {
    expect {
        timeout { puts stderr 'unknow error'; exit 1 }
        eof { exit 0 }
        "password: " { puts stderr "ssh private key unrecognized or does not exists"; exit 1 }
        "Enter passphrase" { puts stderr "private key error or need passpharse"; exit 1 }
        "ssh: connect" { puts stderr "ssh connect error"; exit 1 }
        "(yes/no)? " { send "yes\r" }
    }
}
EOF
EOD;
    }

    protected function expectWithPassphrase($command, $passphrase)
    {
        return <<<EOD
expect << EOF
set timeout 180
spawn {$command}
expect {
    "ssh: connect" { puts stderr "ssh connect error"; exit 1 }
    timeout { puts stderr 'unknow error'; exit 1 }
    "password: " { puts stderr "ssh private key unrecognized"; exit 1 }
    "Enter passphrase" { send "{$passphrase}\r" }
    "(yes/no)? " { send "yes\r" }
}

expect {
    timeout { puts stderr 'unknow error'; exit 1 }
    "password: " { puts stderr "ssh private key unrecognized"; exit 1 }
    "Enter passphrase" { send "{$passphrase}\r" }
    eof { exit 0 }
}

expect  {
    timeout { puts stderr 'unknow error'; exit 1 }
    eof { exit 0 }
    "Enter passphrase" { puts stderr "private key passpharse not match"; exit 1 }
}
EOF
EOD;
    }
}
