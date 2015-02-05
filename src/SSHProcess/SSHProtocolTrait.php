<?php
namespace SSHProcess;

use Symfony\Component\Process\Process;

trait SSHProtocolTrait
{
    protected $originCommand;

    public function getOriginCommand()
    {
        return $this->originCommand;
    }

    protected function expect($command, $timeout = 180)
    {
        $this->originCommand = $command;

        return  <<<EOD
expect << EOF
set timeout {$timeout}
eval exp_spawn {$command}
while (1) {
    expect {
        timeout { puts stderr 'unknow error'; exit 1 }
        eof { break }
        "password: " { puts stderr "ssh private key unrecognized or does not exists"; exit 1 }
        "Enter passphrase" { puts stderr "private key error or need passpharse"; exit 1 }
        "fatal: " { puts stderr "private key error"; exit 1 }
        "Permission denied" { puts stderr "private key error"; exit 1 }
        "ssh: connect" { puts stderr "ssh connect error"; exit 1 }
        "(yes/no)? " { send "yes\r" }
    }
}

set ret [exp_wait]
set pid         [lindex \\\$ret 0]
set spawn_id    [lindex \\\$ret 1]
set os_error    [lindex \\\$ret 2]
set exit_status [lindex \\\$ret 3]

puts stdout "\nExit Status: \\\$exit_status"
puts stdout "OS Error: \\\$os_error"

if {\\\$exit_status != 0} {
    puts stderr "\nExit Status Not 0, Have Errors";
    exit 1;
}

EOF
EOD;
    }

    protected function expectWithPassphrase($command, $passphrase, $timeout = 180)
    {
        $this->originCommand = $command;

        return <<<EOD
expect << EOF
set timeout {$timeout}
eval exp_spawn {$command}

proc judge {} {
    set ret [exp_wait]
    set pid         [lindex \\\$ret 0]
    set spawn_id    [lindex \\\$ret 1]
    set os_error    [lindex \\\$ret 2]
    set exit_status [lindex \\\$ret 3]

    puts stdout "\nExit Status: \\\$exit_status"
    puts stdout "OS Error: \\\$os_error"

    if {\\\$exit_status != 0} {
        puts stderr "\nExit Status Not 0, Have Errors";
        exit 1;
    }
    exit 0
}

expect {
    "ssh: connect" { puts stderr "ssh connect error"; exit 1 }
    timeout { puts stderr 'unknow error'; exit 1 }
    "fatal: " { puts stderr "private key error"; exit 1 }
    "Permission denied" { puts stderr "private key error"; exit 1 }
    "password: " { puts stderr "ssh private key unrecognized"; exit 1 }
    "Enter passphrase" { send "{$passphrase}\r" }
    "(yes/no)? " { send "yes\r" }
    eof { puts stderr "passphrase no use"; judge }
}

expect {
    timeout { puts stderr 'unknow error'; exit 1 }
    "fatal: " { puts stderr "private key error"; exit 1 }
    "Permission denied" { puts stderr "private key error"; exit 1 }
    "password: " { puts stderr "ssh private key unrecognized"; exit 1 }
    "Enter passphrase" { send "{$passphrase}\r" }
    eof { judge }
}

expect  {
    timeout { puts stderr 'unknow error'; exit 1 }
    "fatal: " { puts stderr "private key error"; exit 1 }
    "Permission denied" { puts stderr "private key error"; exit 1 }
    eof { judge }
    "Enter passphrase" { puts stderr "private key passpharse not match"; exit 1 }
}

EOF
EOD;
    }
}
