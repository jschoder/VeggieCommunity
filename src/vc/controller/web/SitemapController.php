<?php
namespace vc\controller\web;

class SitemapController extends \vc\controller\web\AbstractWebController
{
    const CHANGE_ALWAYS = 'always';
    const CHANGE_HOURLY = 'hourly';
    const CHANGE_DAILY = 'daily';
    const CHANGE_WEEKLY = 'weekly';
    const CHANGE_MONTHLY = 'monthly';
    const CHANGE_YEARLY = 'yearly';
    const CHANGE_NEVER = 'never';

    public function handleGet(\vc\controller\Request $request)
    {
        $sixMonthAgo = time() - 15768000;

        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Default pages
        $this->echoUrl('', self::CHANGE_YEARLY, null, 'de', 1.0);
        $this->echoUrl('', self::CHANGE_YEARLY, null, 'en', 1.0);
        $this->echoUrl('account/signup/', self::CHANGE_YEARLY, null, 'de', 1.0);
        $this->echoUrl('account/signup/', self::CHANGE_YEARLY, null, 'en', 1.0);
        $this->echoUrl('chat/', self::CHANGE_YEARLY, null, 'de', 1.0);
        $this->echoUrl('chat/', self::CHANGE_YEARLY, null, 'en', 1.0);
        $this->echoUrl('events/calendar/', self::CHANGE_DAILY, null, 'de', 1.0);
        $this->echoUrl('events/calendar/', self::CHANGE_DAILY, null, 'en', 1.0);
        $this->echoUrl('groups/search/', self::CHANGE_DAILY, null, 'de', 1.0);
        $this->echoUrl('groups/search/', self::CHANGE_DAILY, null, 'en', 1.0);

        $cityMapDe = array(
            'augsburg',
            'berlin',
            'bielefeld',
            'bochum',
            'bonn',
            'bremen',
            'darmstadt',
            'dresden',
            'dortmund',
            'duisburg',
            'düsseldorf',
            'essen',
            'frankfurt',
            'gießen',
            'hamburg',
            'hannover',
            'heidelberg',
            'heilbronn',
            'ingolstadt',
            'jena',
            'karlsruhe',
            'köln',
            'leipzig',
            'mainz',
            'mannheim',
            'münchen',
            'münster',
            'neukölln',
            'nürnberg',
            'oldenburg',
            'osnabrück',
            'regensburg',
            'stuttgart',
            'ulm',
            'wiesbaden',
            'würzburg',
            'wuppertal',

            'graz',
            'innsbruck',
            'linz',
            'salzburg',
            'wien',

            'basel',
            'bern',
            'genf',
            'lausanne',
            'zürich'
        );
        $cityMapEn = array(
            'ann arbor',
            'asheville',
            'ashland',
            'athens',
            'austin',
            'boulder',
            'byron bay',
            'charleston',
            'charlottesville',
            'chartlotte',
            'chicago',
            'columbus',
            'dallas',
            'detroit',
            'durham',
            'fort worth',
            'honolulu',
            'houston',
            'indianapolis',
            'ithaca',
            'jacksonville',
            'kapaa',
            'lawrence',
            'los angeles',
            'nashville',
            'nelson',
            'new york',
            'nyc',
            'northhampton',
            'philadelphia',
            'philadelphia',
            'phoenix',
            'portland',
            'richmond',
            'san antonio',
            'san diego',
            'san fransisco',
            'san jose',
            'santa cruz',
            'seattle',
            'washington',
            'whitehorse',

            'calgary',
            'edmonton',
            'montreal',
            'toronto',
            'vancouver',

            'brighton',
            'bristol',
            'cardiff',
            'edinburgh',
            'glasgow',
            'glasgow',
            'leeds',
            'liverpool',
            'london',
            'manchester',
            'norwich',
            'nottingham',
            'sheffield',

            'adelaide',
            'brisbane',
            'melbourne',
            'perth',
            'queensland',
            'sydney',

            'ghent',
            'paris',
        );
        if ($this->getServer() === 'live') {
            foreach ($cityMapDe as $city) {
                $this->echoUrl('user/result/' . urlencode($city) . '/vegetarisch/', self::CHANGE_WEEKLY, null, 'de', 0.7);
                $this->echoUrl('user/result/' . urlencode($city) . '/vegan/', self::CHANGE_WEEKLY, null, 'de', 0.7);
            }
            foreach ($cityMapEn as $city) {
                $this->echoUrl('user/result/' . urlencode($city) . '/vegetarian/', self::CHANGE_WEEKLY, null, 'en', 0.7);
                $this->echoUrl('user/result/' . urlencode($city) . '/vegan/', self::CHANGE_WEEKLY, null, 'en', 0.7);
            }
        }

        // Profiles
        $profileModel = $this->getDbModel('Profile');
        $profiles = $profileModel->getGooglableProfiles();
        foreach ($profiles as $profileId => $profile) {
            if ($profile['lastUpdate'] < $sixMonthAgo) {
                $changeFrequency = self::CHANGE_YEARLY;
            } else {
                $changeFrequency = self::CHANGE_MONTHLY;
            }

            // German speaking countries
            if (in_array($profile['country'], array(49, 43, 41, 423))) {
                $locale = 'de';
            } else {
                $locale = 'en';
            }
            $this->echoUrl('user/view/' . $profileId . '/', $changeFrequency, $profile['lastUpdate'], $locale, 0.4);
        }

        // Groups
        $groupModel = $this->getDbModel('Group');
        $groupForumModel = $this->getDbModel('GroupForum');

        $groups = $groupModel->loadObjects(
            array(
                'confirmed_at IS NOT NULL',
                'deleted_at IS NULL'
            )
        );
        // Only add forums with content visible to searchengines
        $forums = $groupForumModel->loadObjects(
            array(
                'deleted_at IS NULL',
                'content_visibility' => \vc\object\GroupForum::CONTENT_VISIBILITY_PUBLIC
            )
        );
        $lastForumUpdates = $groupForumModel->getLastUpdatesAllForums();

        $groupMapping = array();
        foreach ($groups as $group) {
            if ($group->language === 'de') {
                $locale = 'de';
            } else {
                $locale = 'en';
            }
            $groupMapping[$group->id] = $group;
            $this->echoUrl('groups/info/' . $group->hashId . '/', self::CHANGE_WEEKLY, null, $locale, 0.4);
        }

        foreach ($forums as $forum) {
            // Make sure only to add forums of visible groups
            if (array_key_exists($forum->groupId, $groupMapping)) {
                // Only index forums with actual content
                if (array_key_exists($forum->id, $lastForumUpdates)) {
                    if ($forum->isMain) {
                        $url = 'groups/forum/' . $groupMapping[$forum->groupId]->hashId . '/';
                    } else {
                        $url = 'groups/forum/' . $groupMapping[$forum->groupId]->hashId . '/' . $forum->hashId . '/';
                    }

                    if ($groupMapping[$forum->groupId]->language === 'de') {
                        $locale = 'de';
                    } else {
                        $locale = 'en';
                    }

                    $this->echoUrl(
                        $url,
                        self::CHANGE_DAILY,
                        $lastForumUpdates[$forum->id],
                        $locale,
                        0.4
                    );
                }
            }
        }

        // Events
        $eventModel = $this->getDbModel('Event');
        $events = $eventModel->getFieldList(
            'hash_id',
            array(
                'start_date > NOW()',
                'deleted_at IS NULL',
                'guest_visibility' => \vc\object\Event::EVENT_VISIBILITY_PUBLIC
            )
        );
        foreach ($events as $eventId) {
            $this->echoUrl('events/view/' . $eventId . '/', self::CHANGE_WEEKLY, null, 'en', 0.4);
        }

        echo '</urlset>';
    }

    private function echoUrl($path, $changeFrequency, $lastModification = null, $locale = 'en', $priority = 0.5)
    {
        echo '<url>';
        echo '<loc>https://www.veggiecommunity.org/' . $locale . '/' . $path . '</loc>';
//        echo '<xhtml:link rel="alternate" hreflang="en" href="https://www.veggiecommunity.org/en/' . $path . '" />';
//        echo '<xhtml:link rel="alternate" hreflang="de" href="https://www.veggiecommunity.org/de/' . $path . '" />';
        if ($lastModification !== null) {
            echo '<lastmod>' . date('Y-m-d', $lastModification) . '</lastmod>';
        }
        echo '<changefreq>' . $changeFrequency . '</changefreq>';
        if ($priority !== 0.5) {
            echo '<priority>' . $priority . '</priority>';
        }
        echo '</url>';
    }
}
