<?php
namespace vc\controller\web\group;

class SearchController extends \vc\controller\web\AbstractWebController
{
    protected function cacheGet()
    {
        return true;
    }

    public function handleGet(\vc\controller\Request $request)
    {
        $this->setTitle(gettext('groups.search.title'));

        $request->assertNumericParam('i', true);

        $groupModel = $this->getDbModel('Group');
        $searchPhrase = $request->getText('s', null);
        $searchSort = $request->getText('sort', 'new');

        if (array_key_exists('i', $_GET)) {
            $currentPage = max(0, intval($_GET['i']));
            $offset = ($currentPage) * \vc\config\Globals::GROUP_ITEMS_SEARCH;
        } else {
            $currentPage = 0;
            $offset = 0;
        }
        $groups = $groupModel->searchGroup(
            $searchPhrase,
            $searchSort,
            $offset,
            \vc\config\Globals::GROUP_ITEMS_SEARCH
        );
        $groupCount = $groupModel->searchGroupCount($searchPhrase);
        $pageCount = ceil($groupCount / \vc\config\Globals::GROUP_ITEMS_SEARCH);
        $this->getView()->set('searchPhrase', $searchPhrase);
        $this->getView()->set('searchSort', $searchSort);
        $this->getView()->set('groups', $groups);

        $pagination = new \vc\object\param\NumericPaginationObject(
            $this->path . 'groups/search/?i=%INDEX%&amp;' .
                (empty($searchPhrase) ? '' : 's=' . urlencode($searchPhrase) . '&amp;') . 'sort=' . urlencode($searchSort),
            $currentPage,
            $pageCount
        );
        $pagination->setDefaultUrl(
            $this->path . 'groups/search/?' .
                (empty($searchPhrase) ? '' : 's=' . urlencode($searchPhrase) . '&amp;') . 'sort=' . urlencode($searchSort)
        );
        $pagination->setDefaultUrlParams(array('%INDEX%' => 0, '%LIMIT%' => 1));
        $this->getView()->set('pagination', $pagination);

        $this->getView()->setHeader('prev', $pagination->getPrev());
        $this->getView()->setHeader('next', $pagination->getNext());

        echo $this->getView()->render('group/search', true);
    }
}
