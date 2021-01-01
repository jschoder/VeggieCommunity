<?php
namespace vc\component;

class PdfComponent extends AbstractComponent
{
    public function __construct()
    {
        // Init PDF-library once when creating the component
        require_once(APP_LIB . '/tcpdf/tcpdf.php');
    }

    public function export($locale, $currentUserId, $contactId, $exportFile = null, $output = 'I')
    {
        $pmModel = $this->getDbModel('Pm');
        $messages = $pmModel->getMessages($currentUserId, $contactId, false, null);

        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getSmallProfiles($locale, array($currentUserId, $contactId));

        if (count($profiles) < 2) {
            throw new \vc\exception\NotFoundException();
        }

        $currentUserProfile = $profiles[0];
        $contactProfile = $profiles[1];

        $pictureModel = $this->getDbModel('Picture');
        $pictureObject = $pictureModel->loadObject(
            array('defaultpic' => 1, 'profileid' => $contactId)
        );
        if ($pictureObject === null) {
            switch ($contactProfile->gender) {
                case 2:
                    $filename = 'default-thumb-m.png';
                    break;
                case 4:
                    $filename = 'default-thumb-f.png';
                    break;
                case 6:
                    $filename = 'default-thumb-o.png';
                    break;
                default:
                    $filename = 'default-thumb-a.png';
            }
            $thumbPic = APP_ROOT . '/web/img/matcha/' . $filename;
        } else {
            $thumbPic = PROFILE_PIC_DIR . '/100x100/' . $pictureObject->filename;
            if (!file_exists($thumbPic)) {
                $pictureSaveComponent = $this->getComponent('PictureSave');
                $pictureSaveComponent->createCropPicture(
                    PROFILE_PIC_DIR,
                    $pictureObject->filename,
                    $thumbPic,
                    100,
                    100
                );
            }
        }

        // create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $margins = $pdf->getMargins();

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(gettext('pdf.author'));
        $pdf->SetTitle(sprintf(gettext('pdf.title'), $currentUserProfile->nickname, $contactProfile->nickname));

        // disable header
        $pdf->setPrintHeader(false);

        // set footer fonts
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        $l = array();
                $l['w_page'] = gettext('pdf.footer') . '   |   ' . gettext('pdf.page') . ':';
                $pdf->setLanguageArray($l);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

        // UserImage
        $pdf->image(
            $thumbPic,
            $margins['left'],
            $margins['top'],
            30,
            30,
            '',
            'https://www.veggiecommunity.org/' . $locale . '/user/view/' . $contactId . '/'
        );

        // Nickname
        $pdf->SetFont(PDF_FONT_NAME_DATA, 'B', 14, '', true);
        $pdf->writeHTMLCell(
            85,
            0,
            $margins['left'] + 33,
            $margins['top'],
            $contactProfile->nickname
        );

        // User ToolTip
        $pdf->SetFont(PDF_FONT_NAME_DATA, '', 12, '', true);
        $htmlBox = '';
        if ($contactProfile->age > 0 && $contactProfile->hideAge !== true) {
            $htmlBox .= " (" . $contactProfile->age . ")";
        }
        if ($contactProfile->nutrition > 0) {
            $htmlBox .= ', ' .
                    \vc\config\Fields::getNutritionCaption(
                        $contactProfile->nutrition,
                        $contactProfile->nutritionFreetext,
                        $contactProfile->gender
                    );
        }
        $htmlBox .= ', ' . $contactProfile->getHtmlLocation();

        $pdf->writeHTMLCell(
            85,
            0,
            $margins['left'] + 33,
            $margins['top'] + 8,
            $htmlBox
        );

        // Infobox
        $pdf->SetFont(PDF_FONT_NAME_DATA, '', 10, '', true);
        $htmlBox = gettext('pdf.firstMessage') . ': ' .
                   date(gettext('pdf.firstMessage.format'), $messages[0]['created']) . '<br />' .
                   gettext('pdf.lastMessage') . ': ' .
                   date(gettext('pdf.lastMessage.format'), $messages[count($messages) - 1]['created']) . '<br />' .
                   gettext('pdf.messageCount') . ': ' .
                   count($messages) . '<br />' .
                   gettext('pdf.currentTimestamp') . ': ' .
                   date(gettext('pdf.currentTimestamp.format'));
        $pdf->writeHTMLCell(
            70,
            0,
            $pdf->getPageWidth() - $margins['right'] - 70,
            $margins['top'],
            $htmlBox
        );

        // Separator
        $pdf->Line(
            $margins['left'],
            45,
            $pdf->getPageWidth() - $margins['right'],
            45
        );


        // Basic Stylesheet
        $pdf->SetFont(PDF_FONT_NAME_DATA, '', 12, '', true);

        // Iterating through messages
        $lastMonth = null;
        $pdf->SetY(48);
        foreach ($messages as $message) {
            $html = '';

            $month = date('m.Y', $message['created']);
            if ($month !== $lastMonth) {
                switch (date('n', $message['created'])) {
                    case 1:
                        $monthName = gettext('date.month.January');
                        break;
                    case 2:
                        $monthName = gettext('date.month.February');
                        break;
                    case 3:
                        $monthName = gettext('date.month.March');
                        break;
                    case 4:
                        $monthName = gettext('date.month.April');
                        break;
                    case 5:
                        $monthName = gettext('date.month.May');
                        break;
                    case 6:
                        $monthName = gettext('date.month.June');
                        break;
                    case 7:
                        $monthName = gettext('date.month.July');
                        break;
                    case 8:
                        $monthName = gettext('date.month.August');
                        break;
                    case 9:
                        $monthName = gettext('date.month.September');
                        break;
                    case 10:
                        $monthName = gettext('date.month.October');
                        break;
                    case 11:
                        $monthName = gettext('date.month.November');
                        break;
                    case 12:
                        $monthName = gettext('date.month.December');
                        break;
                    default:
                        $monthName = '';
                }
                $html .= '<h2>' . $monthName . ' ' . date('Y', $message['created']) . '</h2>';
            }
            $lastMonth = $month;

            $html .= '<p style="border:solid 1px #fff">';
            if (!empty($message['subject'])) {
                $html .= '<strong>' . $message['subject'] . '</strong><br />';
            }
            $html .= '<strong>';
            if ($message['senderid'] == $currentUserProfile->id) {
                $html .= $currentUserProfile->nickname;
            } elseif ($message['senderid'] == $contactProfile->id) {
                $html .= $contactProfile->nickname;
            } else {
                \vc\lib\ErrorHandler::error(
                    'Invalid senderId',
                    __FILE__,
                    __LINE__,
                    array(
                        'senderid' => $message['senderid'],
                        'currentProfileId' => $currentUserProfile->id,
                        'contactProfileId' => $contactProfile->id
                    )
                );
            }
            $html .= '</strong> ' .
                     '<i>(' . date(gettext('pdf.message.timestampFormat'), $message['created']) . ')</i><br />' .
                     nl2br(htmlspecialchars($message['body'])) . '</p>';
            $pdf->writeHTML($html);
        }

        if ($exportFile === null) {
            $exportFile = preg_replace('@([^A-Z,a-z,0-9,\.])@', '', $contactProfile->nickname);
            // Remove any runs of periods (thanks falstro!)
            $exportFile = mb_ereg_replace("([\.]{2,})", '', $exportFile);
            if (empty($exportFile)) {
                $exportFile = $contactProfile->id;
            }
        }
        $pdf->Output($exportFile . '.pdf', $output);
        
        if ($output === 'F') {
            $touched = touch(
                $exportFile . '.pdf', 
                $messages[count($messages) - 1]['created'],
                $messages[count($messages) - 1]['created']
            );
        }
    }
}
