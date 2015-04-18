#!/usr/bin/php
<?php
/**
 * Created by IntelliJ IDEA.
 * User: mland
 * Date: 1/29/15
 * Time: 3:12 PM
 */
/**
 * script to get everything updated for testing
 */

class color
{
    const BLACK = 30;
    const BLUE = 34;
    const GREEN = 32;
    const CYAN = 36;
    const RED = 31;
    const PURPLE = 35;
    const BROWN = 33;
    const WHITE = 37;
    const BWHITE = 97;

    const ON_BLACK = 40;
    const ON_RED = 41;
    const ON_GREEN = 42;
    const ON_YELLOW = 43;
    const ON_BLUE = 44;
    const ON_PURPLE = 45;
    const ON_CYAN = 46;
    const ON_WHITE = 47;
    const ON_BWHITE = 107;

}
trait promptColors
{
    public static function c($color, $string = '', $bold = 0) {
        return "\033[" . ($bold?"1":"0").";" . $color . "m" . $string . self::reset();
    }
    public static function b($backgroundColor, $bold = 0) {
        return "\033[" . ($bold?"1":"0").";" . $backgroundColor . "m";
    }
    public static function bc($backgroundColor, $color, $string, $bold = 0) {
        return "\033[" . ($bold?"1":"0").";" . $backgroundColor . ";" . $color . "m" . $string . self::reset();
    }
    public static function reset() {
        return "\033[0m";
    }

}
trait notify
{
    use promptColors;

    private static function menuNotify($string)
    {
        passthru('clear');
        echo self::b(color::ON_BLACK);
        echo self::bc(color::ON_BWHITE, color::BLACK, " [ " . $string . " ] ", 0) . PHP_EOL;
    }
    private static function actionNotify($string)
    {
        echo self::bc(color::ON_CYAN, color::BLACK, " $string ") . PHP_EOL;
    }
    private static function infoNotify($string)
    {
        echo self::c(color::ON_GREEN, " " . $string . " ") . PHP_EOL;
    }
    private static function warningNotify($string)
    {
        echo self::c(color::ON_RED, " " . $string . " ") . PHP_EOL;
    }
    private static function optionNotify($option, $string)
    {
        echo self::c(color::ON_GREEN, " {$option} ", 1) . " => " . self::bc(color::ON_BLACK, color::WHITE, " {$string} ") . PHP_EOL;
    }
    private static function currentOptionNotify($option, $string)
    {
        echo self::c(color::ON_WHITE, " {$option} ", 1) . " => " . self::bc(color::ON_BLACK, color::WHITE, " {$string} ") . PHP_EOL;
    }
    private static function notify($string = '')
    {
        echo self::bc(color::ON_BLACK, color::ON_WHITE, $string) . PHP_EOL;
    }
}
trait prompt
{
    private static function getInput($promptString = '')
    {
        if (strlen($promptString) > 0) {
            echo self::bc(color::ON_CYAN, color::BLACK, " $promptString ") . " " ;
        }
        $handle = fopen ("php://stdin","r");
        return str_replace("\n", "", trim(fgets($handle)));
    }
}

class upstart
{
    use notify;
    use prompt;

    const MY_PATH = '/your/path/here';
    const MAIN_MENU_BRANCH =               'b';
    const MAIN_MENU_UPDATE =               'u';
    const MAIN_MENU_DB_REFRESH =           'd';
    const MAIN_MENU_QUIT =                 'q';
    const MAIN_MENU_SELF_UPDATE =          's';
    const MAIN_MENU_ENVIRONMENT_CHECK =    'c';
    const MAIN_MENU_ENVIRONMENT_LAUNCH =   'l';
    const MAIN_MENU_ENVIRONMENT_HALT =     'h';
    const MAIN_MENU_GET_CURRENT_BRANCH =   'g';
    const MAIN_MENU_TEST =                 't';
    const MAIN_MENU_VIEW_PHP_ERRORS =      'e';

    private static $options = [
        self::MAIN_MENU_GET_CURRENT_BRANCH =>   'Current Branch Name',
        self::MAIN_MENU_BRANCH =>               'Branch Options',
        self::MAIN_MENU_VIEW_PHP_ERRORS =>      'View Apache Errors',
        self::MAIN_MENU_DB_REFRESH =>           'Refresh Database',
        self::MAIN_MENU_UPDATE =>               'Update Projects',
        self::MAIN_MENU_TEST =>                 'Launch Selenium Tests',
        self::MAIN_MENU_SELF_UPDATE =>          'Self-Update Upstart',
        self::MAIN_MENU_ENVIRONMENT_CHECK =>    'Check Selenium Test Environment',
        self::MAIN_MENU_ENVIRONMENT_LAUNCH =>   'Launch Selenium Test Environment',
        self::MAIN_MENU_ENVIRONMENT_HALT =>     'Halt Selenium Test Environment',
        self::MAIN_MENU_QUIT =>                 'Quit'
    ];

    private static $services = [
        "vagrant"   => "5555",
        "mysql"     => "3306",
        "apache"    => "8888",
        "selenium"  => "4444"
    ];

    public static function mainMenu($switch = '')
    {
        switch (strtolower($switch)) {
            case self::MAIN_MENU_BRANCH:
                self::menuNotify(self::$options[self::MAIN_MENU_BRANCH]);
                $branches = self::listBranches();
                $selectedBranchId = self::getInput("Checkout which branch number ? (b for back, q for quit)");
                if ($selectedBranchId === 'b') {
                    break;
                }
                if ($selectedBranchId === 'q') {
                    exit();
                }
                self::checkOutUpdateBranch($branches, $selectedBranchId);
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_UPDATE:
                self::menuNotify(self::$options[self::MAIN_MENU_UPDATE]);

                //@todo replace removed dependencys
                self::infoNotify("on branch " . self::getCurrentBranchName());
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_DB_REFRESH:
                self::menuNotify(self::$options[self::MAIN_MENU_DB_REFRESH]);
                $input = self::getInput("Are you sure? (b for back, q for quit)");
                if ($input === 'q') {
                    self::quit();
                }
                if ($input !== 'y') {
                    break;
                }
                //let user see output
                passthru('ant db-sync-hot-tables');
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_QUIT:
                self::quit();
                break; //nothing

            case self::MAIN_MENU_TEST:
                self::menuNotify(self::$options[self::MAIN_MENU_TEST]);
                $tests = self::listTests();
                $selectedBranchId = self::getInput("run which test ? (b for back, q for quit)");
                if ($selectedBranchId === 'q') {
                    self::quit();
                }
                if ($selectedBranchId === 'b') {
                    break;
                }
                self::menuNotify("Run test: " . $selectedBranchId);
                self::runTest($tests, $selectedBranchId);
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_SELF_UPDATE:
                self::menuNotify(self::$options[self::MAIN_MENU_SELF_UPDATE]);
                self::selfUpdate();
                self::infoNotify("done");
                self::waitForUserBack();
                passthru('./upstart.php');
                exit();

            case self::MAIN_MENU_ENVIRONMENT_CHECK:
                self::menuNotify(self::$options[self::MAIN_MENU_ENVIRONMENT_CHECK]);
                self::checkEnvironment();
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_ENVIRONMENT_LAUNCH:
                self::menuNotify(self::$options[self::MAIN_MENU_ENVIRONMENT_LAUNCH]);
                self::launchEnvironment();
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_ENVIRONMENT_HALT:
                self::menuNotify(self::$options[self::MAIN_MENU_ENVIRONMENT_HALT]);
                self::haltEnvironment();
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_GET_CURRENT_BRANCH:
                self::menuNotify(self::$options[self::MAIN_MENU_GET_CURRENT_BRANCH]);
                self::infoNotify("on branch: " . self::getCurrentBranchName());
                self::waitForUserBack();
                break;

            case self::MAIN_MENU_VIEW_PHP_ERRORS:
                self::menuNotify(self::$options[self::MAIN_MENU_VIEW_PHP_ERRORS]);
                self::getApacheErrors();
                self::waitForUserBack();
                break;

            default:
                if (strlen($switch)) {
                    self::notify('fat fingered: ' . $switch);
                }
        }
        self::askMainMenu();
    }

    private static function quit()
    {
        self::infoNotify('goodbye');
        self::reset();
        exit();
    }

    private static function waitForUserBack()
    {
        $input = self::getInput("(b for back, q for quit)");
        if ($input === 'q') {
            self::quit();
        }
    }

    private static function selfUpdate()
    {
        self::infoNotify('updating script');
        shell_exec('git pull');
    }

    private static function askMainMenu()
    {
        self::menuNotify('Upstart Main Menu');
        foreach (self::$options as $index => $command) {
            self::optionNotify($index, $command);
        }
        self::mainMenu(self::getInput('Your choice?'));
    }

    private static function listBranches()
    {
        self::menuNotify('Remote Branches');
        shell_exec("cd " . self::MY_PATH . " && git fetch -p > /dev/null 2>&1");
        $branches = shell_exec("cd " . self::MY_PATH . " && git remote prune origin > /dev/null 2>&1 && git branch -r");
        $branches = explode("\n", $branches);
        $myBranch = self::getCurrentBranchName();
        $myBranchFound = false;
        foreach ($branches as $branchNum => $branchName) {
            if (! strlen(trim($branchName))) {
                unset($branches[$branchNum]);
                continue;
            }

            $branches[$branchNum] = str_replace("origin/", "", trim($branchName));
            //self::notify("'".$branches[$branchNum] ."'". " " . "'".$myBranch."'");
            if ($branches[$branchNum] === $myBranch) {
                $myBranchFound = true;
                self::currentOptionNotify(($branchNum + 1), $branches[$branchNum]);
            } else {
                self::optionNotify(($branchNum + 1), $branches[$branchNum]);
            }
        }
        if (! $myBranchFound) {
            self::warningNotify("current branch not found: $myBranch");
            $input = self::getInput("Track your branch?");
            if ($input === 'y') {
                self::_trackMyBranch();
                self::infoNotify("Origin is now tracking " . $myBranch);
                self::waitForUserBack();
            }
        }
        return $branches;
    }

    private static function checkOutUpdateBranch(array $branchList, $requestedBranchId = 0)
    {
        if (! is_numeric($requestedBranchId)) {
            throw new Exception("invalid branch number: ". $requestedBranchId);
        }
        $requestedBranchId--;  //bring the index and what was shown to the user back in sync
        if (! isset($branchList[$requestedBranchId])) {
            throw new Exception("choose a valid branch");
        }
        $requestedBranchName = $branchList[$requestedBranchId];
        self::infoNotify("requested branch is  [$requestedBranchName]");

        //prune
        shell_exec("cd " . self::MY_PATH . " && git remote prune origin > /dev/null 2>&1");

        //current branch
        if (self::getCurrentBranchName() === $requestedBranchName) {
            self::infoNotify("current branch is the requested branch, updating");
            return shell_exec("cd " . self::MY_PATH . " && git fetch");
        }

        //get checked out branches
        $localBranches = explode("\n", shell_exec("cd " . self::MY_PATH . " && git branch"));

        //already checked out?
        foreach ($localBranches as $index => $localBranchName) {
            //remove empty lines
            if (! strlen(trim($localBranchName))) {
                unset($localBranches[$index]);
                continue;
            }

            $localBranchName = trim($localBranchName);

            if ($localBranchName === $requestedBranchName) { //already tracking it
                self::infoNotify("requested branch is already checked out, switching and updating");
                return shell_exec("cd " . self::MY_PATH . " && git pull && git checkout $localBranchName");
            }
        }
        //new branch retrieve
        self::infoNotify("checking out branch for the first time");
        $command = "git checkout --track -b ".str_replace("origin/", "", $requestedBranchName)." origin/" . $requestedBranchName;
        shell_exec("cd " . self::MY_PATH . " && " . $command);
    }

    private static function _trackMyBranch()
    {
        shell_exec("cd " . self::MY_PATH . " &&  git push -u origin " . self::getCurrentBranchName());
    }

    private static function listTests()
    {
        $tests = [
            "smoke" => 'ant smoke',
            "full" => 'ant test',
            "statements" => "phpunit --testsuite Statement",
            "facility" => "phpunit --testsuite Facility",
            "signup" => "phpunit --testsuite Signup"
        ];

        foreach ($tests as $key => $command) {
            self::optionNotify($key, $command);
        }
        return $tests;
    }

    private static function runTest(array $testCommands, $testName = '')
    {
        if (! isset($testCommands[$testName])) {
            throw new Exception("invalid test command");
        }
        passthru("cd " . self::MY_PATH . " && " . str_replace('phpunit', 'phpunit',  $testCommands[$testName]));
    }

    private static function launchEnvironment()
    {
        self::checkNmap();
        $response = shell_exec("nmap localhost");
        foreach (self::$services as $service => $port) {
            $running = strpos($response, $port);
            if ($running !== false) {
                self::infoNotify("$service already running");
                continue;
            }
            self::infoNotify("Attempting to launch $service.");
            self::$service(true);
        }
    }

    public static function haltEnvironment()
    {
        foreach (self::$services as $service => $port) {
            self::infoNotify("stopping $service");
            self::$service(false);
        }
    }

    public static function checkEnvironment()
    {
        self::checkNmap();
        $response = shell_exec("nmap localhost");

        $errorFound = false;
        foreach (self::$services as $service => $port) {
            //check if running
            $running = strpos($response, $port);

            if ($running !== false) {
                self::infoNotify("$service is listening");
            } else {
                $errorFound = true;
                self::warningNotify("$service is not listening");
            }
            if ($service === 'mysql') {
                $specialResponse = shell_exec("mysql -h 127.0.0.1  2>&1");
                if (stripos($specialResponse, 'Lost connection') !== false) {
                    $errorFound = true;
                    self::warningNotify("mysql is not running in vagrant");
                }
                if (stripos($specialResponse, 'Access denied') !== false) {
                    self::infoNotify("mysql confirmed working");
                }
            }
            if ($service === 'apache') {
                $specialResponse = shell_exec("curl mysite.localhost:8888 2>&1");
                if (stripos($specialResponse, 'Empty reply') !== false) {
                    $errorFound = true;
                    self::warningNotify("apache is not running in vagrant");
                }
                if (stripos($specialResponse, 'Received') !== false) {
                    self::infoNotify("apache confirmed working");
                }
            }
        }
        if ($errorFound) {
            self::warningNotify("Not all services are available. Automated testing will malfunction.");
        }
    }

    private static function apache($start)
    {
        if ($start) {
            shell_exec("ant httpd-bare-up");
        } else {
            shell_exec("ant httpd-bare-down");
        }
    }

    private static function mysql($start)
    {
        if ($start) {
            shell_exec("ant mysql-bare-up");
        } else {
            shell_exec("ant mysql-bare-down");
        }
    }

    private static function vagrant($start)
    {
        if ($start) {
            shell_exec("vagrant up");
        } else {
            shell_exec("vagrant halt");
        }
    }

    private static function selenium($start)
    {
        $selenium = trim(shell_exec("ls /Applications | grep selenium"));
        if ($selenium == "") {
            self::warningNotify("Selenium was not found in the Applications folder.");
            return;
        }

        if ($start) {
            $command = "java -jar /Applications/$selenium ";
            $output = 'selenium.log';
            $pidfile = 'selenium.pid';
            $full = 'nohup '.$command.' &';
            //echo $full . PHP_EOL;
            exec($full);
            //sleep(5);
        } else {
            shell_exec("killall java");
        }
    }



    public static function updateMyProject()
    {
        self::infoNotify('updating my project');
        shell_exec("cd ".self::MY_PATH." && git pull");
    }

    private static function getCurrentBranchName()
    {
        return str_replace("\n", "", shell_exec("cd  " . self::MY_PATH . " && git rev-parse --abbrev-ref HEAD"));
    }

    public static function checkNmap()
    {
        if (strlen(shell_exec('which nmap')) > 0) {
            return;
        }
        self::infoNotify("installing nmap");
        shell_exec("brew install nmap");
    }

    private static function getApacheErrors()
    {
        passthru("ssh -t vagrant@127.0.0.1 -p 5555 -i ~/.vagrant.d/insecure_private_key 'tail /var/log/httpd/myproject.error.log -n 50'");
    }
}
define('HOME', str_replace("\n", '', shell_exec('pwd')));
upstart::mainMenu();
