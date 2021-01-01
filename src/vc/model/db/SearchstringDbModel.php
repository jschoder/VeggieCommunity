<?php
namespace vc\model\db;

class SearchstringDbModel extends AbstractDbModel
{
    const DB_TABLE = 'vc_searchstring_index';

    public function updateIndex($profileid)
    {
        $profileModel = $this->getDbModel('Profile');

        $search = array();
        $query = 'SELECT field_value FROM vc_profile_field_search
                  WHERE profile_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array($profileid));
        $statement->bind_result($fieldValue);
        while ($statement->fetch()) {
            $search[] = $fieldValue;
        }
        $statement->close();

        $political = array();
        $query = 'SELECT field_value FROM vc_profile_field_political
                  WHERE profile_id = ?';
        $statement = $this->getDb()->queryPrepared($query, array($profileid));
        $statement->bind_result($fieldValue);
        while ($statement->fetch()) {
            $political[] = $fieldValue;
        }
        $statement->close();

        $query = "SELECT "
             . " vc_profile.id, "
             // Keyword fields
             . " nickname, zodiac,nutrition,smoking,alcohol,religion,children,"
             . " marital,bodyheight,bodytype,clothing,haircolor,eyecolor,relocate,"
             // Basic infos
             . " postalcode,residence,region,vc_country.name_de, vc_country.name_en,"
             // Hide/show questionaires
             . " tabQuestionaire1Hide,tabQuestionaire2Hide,tabQuestionaire3Hide,"
             . " tabQuestionaire4Hide,tabQuestionaire5Hide,"
             // Specific texts
             . " word1,word2,word3,"
             // Contact
             . " homepage, favlink1, favlink2, favlink3"
             . " FROM vc_profile,vc_country "
             . " WHERE vc_profile.country = vc_country.id AND vc_profile.id=" . intval($profileid);


        $result = $this->getDb()->select($query);
        $row = $result->fetch_row();
        $textvalues_all = array();
        $textvalues_profiles = array();

        $index=0;
        $profileid2 = $row[$index];
        $index++;
        if ($profileid != $profileid2) {
            \vc\lib\ErrorHandler::error(
                "Error while creating searchIndex for profile " . $profileid . "\n" . $query,
                __FILE__,
                __LINE__
            );
            return;
        }
        $nickname = $row[$index];
        $index++;
            $textvalues_profiles[] = $nickname;
            $textvalues_all[] = $nickname;

        $zodiac = $row[$index];
        $index++;
        $nutrition = $row[$index];
        $index++;
        $smoking = $row[$index];
        $index++;
        $alcohol = $row[$index];
        $index++;
        $religion = $row[$index];
        $index++;
        $children = $row[$index];
        $index++;
        $marital = $row[$index];
        $index++;
        $bodyheight = $row[$index];
        $index++;
        $bodytype = $row[$index];
        $index++;
        $clothing = $row[$index];
        $index++;
        $haircolor = $row[$index];
        $index++;
        $eyecolor = $row[$index];
        $index++;
        $relocate = $row[$index];
        $index++;

        // Generic values
        $postalcode = $row[$index];
        $index++;
        $residence = $row[$index];
        $index++;
        $region = $row[$index];
        $index++;
        $country_de = $row[$index];
        $index++;
        $country_en = $row[$index];
        $index++;
        $textvalues_all[] = $postalcode;
        $textvalues_all[] = $residence;
        $textvalues_all[] = $region;
        $textvalues_all[] = $country_de;
        $textvalues_all[] = $country_en;
        $textvalues_profiles[] = $postalcode;
        $textvalues_profiles[] = $residence;
        $textvalues_profiles[] = $region;
        $textvalues_profiles[] = $country_de;
        $textvalues_profiles[] = $country_en;

        // Special text values
        $tabQuestionaire1Hide = $row[$index];
        $index++;
        $tabQuestionaire2Hide = $row[$index];
        $index++;
        $tabQuestionaire3Hide = $row[$index];
        $index++;
        $tabQuestionaire4Hide = $row[$index];
        $index++;
        $tabQuestionaire5Hide = $row[$index];
        $index++;
        $word1 = $row[$index];
        $index++;
        $word2 = $row[$index];
        $index++;
        $word3 = $row[$index];
        $index++;
        if (!$tabQuestionaire1Hide) {
            $textvalues_all[] = $word1;
            $textvalues_all[] = $word2;
            $textvalues_all[] = $word3;
        }
        $textvalues_profiles[] = $word1;
        $textvalues_profiles[] = $word2;
        $textvalues_profiles[] = $word3;

        // Contactdata
        $homepage = $row[$index];
        $index++;
        $favlink1 = $row[$index];
        $index++;
        $favlink2 = $row[$index];
        $index++;
        $favlink3 = $row[$index];
        $index++;

        $textvalues_profiles[] = $homepage;
        $textvalues_profiles[] = $favlink1;
        $textvalues_profiles[] = $favlink2;
        $textvalues_profiles[] = $favlink3;

        $result->free();

        $query2 = "SELECT topic, content FROM vc_questionaire WHERE profileid=" . intval($profileid);
        $result2 = $this->getDb()->select($query2);
        $questionaireLength = 0;

        while ($row2 = $result2->fetch_row()) {
            if (($row[0]==1 && !$tabQuestionaire1Hide) ||
                ($row[0]==2 && !$tabQuestionaire2Hide) ||
                ($row[0]==3 && !$tabQuestionaire3Hide) ||
                ($row[0]==4 && !$tabQuestionaire4Hide) ||
                ($row[0]==5 && !$tabQuestionaire5Hide)) {
               // Hobbies are always visible only to registered user
                $textvalues_all[] = $row2[1];
            }
            $questionaireLength += strlen($row2[1]);
            $textvalues_profiles[] = $row2[1];
        }
        $result2->free();

        // Update questionaire-length-field in profile
        $profileModel->update(
            array(
                'id' => intval($profileid)
            ),
            array(
                'questionairelength' => intval($questionaireLength)
            )
        );

        // Keywords
        $textvalues_profiles_de = $textvalues_profiles;
        $textvalues_profiles_en = $textvalues_profiles;
        $textvalues_all_de = $textvalues_all;
        $textvalues_all_en = $textvalues_all;

        $keywords_query = array();
        foreach ($search as $searchValue) {
            if (is_numeric($searchValue) && $searchValue > 1) {
                $keywords_query[] = "(field='SEARCH' AND value=" . $searchValue . ")";
            }
        }
        if (is_numeric($zodiac) && $zodiac > 1) {
            $keywords_query[] = "(field='ZODIAC' AND value=" . $zodiac . ")";
        }
        if (is_numeric($nutrition) && $nutrition > 1) {
            $keywords_query[] = "(field='NUTRIT' AND value=" . $nutrition . ")";
        }
        if (is_numeric($smoking) && $smoking > 1) {
            $keywords_query[] = "(field='SMOKE' AND value=" . $smoking . ")";
        }
        if (is_numeric($alcohol) && $alcohol > 1) {
            $keywords_query[] = "(field='ALCOH' AND value=" . $alcohol . ")";
        }
        if (is_numeric($religion) && $religion > 1) {
            $keywords_query[] = "(field='RELIGI' AND value=" . $religion . ")";
        }
        if (is_numeric($children) && $children > 1) {
            $keywords_query[] = "(field='CHILD' AND value=" . $children . ")";
        }
        foreach ($political as $politicalviewValue) {
            if (is_numeric($politicalviewValue) && $politicalviewValue > 1) {
                $keywords_query[] = "(field='POLITC' AND value=" . $politicalviewValue . ")";
            }
        }
        if (is_numeric($marital) && $marital > 1) {
            $keywords_query[] = "(field='MARTIL' AND value=" . $marital . ")";
        }
        if (is_numeric($bodyheight) && $bodyheight > 1) {
            $keywords_query[] = "(field='HEIGHT' AND value=" . $bodyheight . ")";
        }
        if (is_numeric($bodytype) && $bodytype > 1) {
            $keywords_query[] = "(field='BDTYPE' AND value=" . $bodytype . ")";
        }
        if (is_numeric($clothing) && $clothing > 1) {
            $keywords_query[] = "(field='CLOTH' AND value=" . $clothing . ")";
        }
        if (is_numeric($haircolor) && $haircolor > 1) {
            $keywords_query[] = "(field='HAIR' AND value=" . $haircolor . ")";
        }
        if (is_numeric($eyecolor) && $eyecolor > 1) {
            $keywords_query[] = "(field='EYE' AND value=" . $eyecolor . ")";
        }
        if (is_numeric($relocate) && $relocate > 1) {
            $keywords_query[] = "(field='RELCOT' AND value=" . $relocate . ")";
        }

        if (!empty($keywords_query)) {
            $query = "SELECT locale, keyword FROM vc_keyword WHERE " . implode(" OR ", $keywords_query);
            $result = $this->getDb()->select($query);
            while ($row2 = $result->fetch_row()) {
                $locale = $row2[0];
                $keyword = $row2[1];
                if ($locale == 'de') {
                    $textvalues_profiles_de[] = $keyword;
                    $textvalues_all_de[] = $keyword;
                } elseif ($locale == 'en') {
                    $textvalues_profiles_en[] = $keyword;
                    $textvalues_all_en[] = $keyword;
                }
            }
            $result->free();
        }

        // Hobbies
        $query = "SELECT name_de, description_de, name_en, description_en FROM vc_hobby WHERE "
                . "id IN (SELECT hobbyid FROM vc_profile_hobby WHERE profileid=" . intval($profileid) . ")";
        $result = $this->getDb()->select($query);
        while ($row2 = $result->fetch_row()) {
            $textvalues_profiles_de[] = $row2[0];
            $textvalues_profiles_de[] = $row2[1];
            $textvalues_profiles_en[] = $row2[2];
            $textvalues_profiles_en[] = $row2[3];
        }
        $result->free();

        $this->insertSearchIndex($profileid, 'de', 0, $textvalues_profiles_de);
        $this->insertSearchIndex($profileid, 'en', 0, $textvalues_profiles_en);
        $this->insertSearchIndex($profileid, 'de', 1, $textvalues_all_de);
        $this->insertSearchIndex($profileid, 'en', 1, $textvalues_all_en);
    }

    private function insertSearchIndex($profileId, $locale, $visibility, &$textarray)
    {
        $searchtext = implode(' ', $textarray);
        $query = 'INSERT INTO vc_searchstring_index ' .
                 'SET profileid = ?, locale = ?, visibility = ?, searchtext = ? ' .
                 'ON DUPLICATE KEY UPDATE searchtext = ?';
        $this->getDb()->executePrepared(
            $query,
            array(
                intval($profileId),
                $locale,
                $visibility,
                $searchtext,
                $searchtext
            )
        );
    }
}
