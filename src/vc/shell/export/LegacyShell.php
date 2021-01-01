<?php
namespace vc\shell\export;

class LegacyShell extends \vc\shell\AbstractShell
{
    public function run()
    {
    
        try {        
            $statement = $this->getDb()->queryPrepared(
                'SELECT id, nickname, email, vc_setting.value AS locale, active FROM vc_profile LEFT JOIN vc_setting ON vc_setting.profileid = vc_profile.id AND vc_setting.field = 31 ORDER BY id DESC'
            );
        
            $statement->bind_result(
                $id,
                $nickname,
                $email,
                $locale,
                $active
            );
            $userNameMapping = array();
            $activeUsers = array();
            while ($statement->fetch()) {
                $userNameMapping[$id] = $nickname;
                if ($active > 0) {
                    $activeUsers[] = array(
                        $id,
                        $nickname,
                        $email,
                        $locale
                    );
                }
            }
            $statement->close();
            
            print count($activeUsers) . ' Emails to create' . "\n";
            
            
          //$this->createAndSend($userNameMapping, 1, 'Joachim', 'vc-rd@jschoder.de', 'de');

            
            foreach ($activeUsers as $user) {
            
            // :TODO: REMOVE RESTRICTION
                //if ($user[0] > 1000 || $user[0] === 1) {
                //    continue;
                //}
            
                $this->createAndSend($userNameMapping, $user[0], $user[1], $user[2], $user[3]);
            }
            
            print 'All messages sent!!!' . "\n";
        } catch (Exception $e) {
            echo 'Start failed :: ';
            echo $e->getMessage();
        }
    }
    
    private function createAndSend($userNameMapping, $id, $nickname, $email, $locale) {
        try {
            print 'Create ' . $nickname . ' (' . $id . ') ';
            
            
            $pdfComponent = $this->getComponent('Pdf');
            $i14nComponent = $this->getComponent('I14n');
            $i14nComponent->loadLocale($locale);              

            $contactIds = $this->getContactIds($id);
            echo '[' . count($contactIds) . '] ';
            
            if (count($contactIds) === 0) {
                echo ' no';
                return;
            }
            
            $tempFiles = array();
            foreach ($contactIds as $contactId) {
            
                try {
                    $exportFile = preg_replace('@([^A-Z,a-z,0-9,\.])@', '', $userNameMapping[$contactId]);
                    // Remove any runs of periods (thanks falstro!)
                    $exportFile = mb_ereg_replace("([\.]{2,})", '', $exportFile);
                    if (empty($exportFile)) {
                        $exportFile = $contactId;
                    }

                    $pdfComponent->export(
                        $locale,
                        $id,
                        intval($contactId),
                        realpath('../../legacy/temp') . '/' . $exportFile,
                        'F'
                    );

                    $tempFiles[] = $exportFile . '.pdf'; 
                    
                    echo '.';
                } catch (\vc\exception\NotFoundException $e) {
                    echo '-{' . $contactId . '}';
                }
            }
            
            echo 'Z ';
            $archive = new \ZipArchive();
            $archive->open(realpath('../../legacy') . '/' . $id . '.zip', \ZipArchive::CREATE);
            foreach ($tempFiles as $tempFile) {
                $archive->addFile(realpath('../../legacy/temp') . '/' . $tempFile, $tempFile);
            }
            $archive->close();
            
            echo 'D ';
            foreach ($tempFiles as $tempFile) {
                unlink(realpath('../../legacy/temp') . '/' . $tempFile);
            }
            
    //    $mailComponent = $this->getComponent('Mail');
            
            print ' ok' . "\n";
        
        } catch (Exception $e) {
            echo 'Create ' . $id . ' failed!!! ' . "\n";
            echo $e->getMessage();
        }
    }
    
    private function getContactIds($id) {
    
        $ids = [];
        $query = 'SELECT DISTINCT senderid, recipientid FROM vc_message WHERE (senderid = ? AND senderstatus > 0) OR (recipientid = ? AND recipientstatus > 0)';
        $statement = $this->getDb()->queryPrepared($query, array(intval($id), intval($id)));
        $groups = array();
        $statement->bind_result(
            $senderId,
            $recipientId
        );
        while ($statement->fetch()) {
            if ($senderId == $id) {
                //echo '   A   ::   ' . $senderid . '   ::   ' . $red
                $ids[] = $recipientId;
            } else {
                $ids[] = $senderId;
            }
        }
        $statement->close();
        return $ids;
    }
}
