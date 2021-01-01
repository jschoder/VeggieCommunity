<?php
// :TODO: add tab for saved searches
$tabs = array(
array('path' => 'user/search',
'caption' => gettext('menu.search')),
array('path' => 'user/search/saved',
'caption' => gettext('menu.search.saved')),
);
if (!empty($this->requestQuery) && count($this->requestQuery) > 1) {
$this->requestQuery['index'] = 0;
$resultQuery = implodeQuery($this->requestQuery);
unset($this->requestQuery['index']);
unset($this->requestQuery['limit']);
$searchQuery = implodeQuery($this->requestQuery);
$tabs[0]['href'] = $this->path . 'user/search/?' . $searchQuery;
$tabs[] = array(
'path' => 'user/result',
'href' => $this->path . 'user/result/?' . $resultQuery,
'caption' => gettext('menu.result'));
}
echo $this->element('tabs.links',
array('class' => 'majorTabs',
'tabs' => $tabs,
'path' => $this->path,
'site' => $this->site));