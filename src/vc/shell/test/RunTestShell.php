<?php
namespace vc\shell\test;

class RunTestShell extends \vc\shell\AbstractShell
{
    private $currentTest = '---';

    private $errors = array();

    private $totalErrorCount = 0;

    private $totalCheckedUrls = 0;

    public function run()
    {
        echo "Execute Test \n";
        $start = time();

        $rootUrl = $this->getParam('rootUrl', 'https://www.veggiecommunity.dev');
        $paths = explode("\n", file_get_contents(APP_LIB . '/urls.txt'));
        $locales = array('de', 'en');
        $designs = array('matcha', 'lemongras');
        $users = array(
            array(null, null),
            array('1', 'local'),
            array('49805', 'local')
        );

        $profileModel = $this->getDbModel('Profile');
        $statement = $profileModel->getDb()->queryPrepared(
            'SELECT id FROM vc_profile WHERE active > 0 ORDER BY rand() LIMIT 50'
        );
        $statement->bind_result(
            $profileId
        );
        while ($statement->fetch()) {
            $paths[] = 'user/view/' . $profileId . '/';
            $paths[] = 'user/view/' . $profileId . '/album/';
            $paths[] = 'user/view/' . $profileId . '/groups/';
            $paths[] = 'user/view/' . $profileId . '/friends/';
        }
        $statement->close();

        array_unique($paths);

        foreach ($users as $user) {
            foreach ($locales as $locale) {
                $urls = array();
                foreach ($paths as $path) {
                    if (in_array(
                        $path,
                        array(
                            'sitemap.xml'
                        )
                    )) {
                        $urls[] = '/' . $path;
                    } else {
                        $urls[] = '/' . $locale . '/' . $path;
                    }
                }

                $this->currentTest = $locale . '-' . $user[0];

                $testJob = new TestJob(
                    $this,
                    $rootUrl,
                    $user[0],
                    $user[1],
                    $locale,
                    $designs,
                    boolval($this->getParam('follow', false)),
                    $urls
                );
                $testJob->run();

                $this->totalCheckedUrls += count($testJob->getCheckedUrls());

                echo "\n\n";
            }
        }

        if ($this->getServer() == 'local') {
            echo "Cleanup \n";
            $this->getDb()->execute('TRUNCATE vc_suspicion');
        }

        $end = time();

        echo "Done \n";
        echo "The test took " . ($end - $start) . " seconds.\n";
        echo $this->totalErrorCount . " errors found.\n";
        echo $this->totalCheckedUrls . " urls checked.\n";
    }

    public function addError($error) {
        echo 'e';
        if (!array_key_exists($this->currentTest, $this->errors)) {
            $this->errors[$this->currentTest] = array();
        }
        $this->errors[$this->currentTest][] = $error;
        $this->totalErrorCount++;
        file_put_contents('./testErrors.log', var_export($this->errors, true));
    }
}
