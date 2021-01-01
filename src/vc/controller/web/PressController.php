<?php
namespace vc\controller\web;

class PressController extends AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }
    
    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('menu.press'));

//    $tabID = getTab(0, 'aboutus');
//    $data['TAB_LINKS'] = true;
//    $data['TAB_CACHE'] = true;
//    $data['TABS'] = array();
//    $data['TABS']['aboutus'] = array(
//        'CAPTION'=>gettext('press.tab.aboutus'),
//        'URL'=> $this->path . $this->site . '/aboutus/',
//        'AUTO_LOAD'=>$tabID=='aboutus',
//        'VISIBLE'=>$tabID=='aboutus',
//        'LOGIN_ONLY'=>false);
//    $data['TABS']['contact'] = array(
//        'CAPTION'=>gettext('press.tab.contact'),
//        'URL'=> $this->path . $this->site . '/contact/',
//        'AUTO_LOAD'=>$tabID=='contact',
//        'VISIBLE'=>$tabID=='contact',
//        'LOGIN_ONLY'=>false);
//    $data['TABS']['downloads'] = array(
//        'CAPTION'=>gettext('press.tab.downloads'),
//        'URL'=> $this->path . $this->site . '/downloads/',
//        'AUTO_LOAD'=>$tabID=='downloads',
//        'VISIBLE'=>$tabID=='downloads',
//        'LOGIN_ONLY'=>false);
//    $data['TABS']['pressreleases'] = array(
//        'CAPTION'=>gettext('press.tab.pressreleases'),
//        'URL'=> $this->path . $this->site . '/pressreleases/',
//        'AUTO_LOAD'=>$tabID=='pressreleases',
//        'VISIBLE'=>$tabID=='pressreleases',
//        'LOGIN_ONLY'=>false);


        echo $this->getView()->render('press', true);
    }
}
