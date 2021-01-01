<h1>Flags</h1>
<div class="jThreads"><?php
foreach ($this->threads as $thread) {
?><h2><?php
if ($thread->contextType === vc\config\EntityTypes::PROFILE) {
echo 'Profile Feed #' . $thread->contextId;
} else if ($thread->contextType === vc\config\EntityTypes::EVENT) {
echo 'Event #' . $thread->contextId;
} else {
echo 'Unknown type';
}
?></h2><?php
echo $this->element('forum/thread',
array('path' => $this->path,
'imagesPath' => $this->imagesPath,
'displayAuthors' => true,
'thread' => $thread,
'currentUser' => $this->currentUser,
'isLikable' => false,
'canPostComment' => false,
'ownPicture' => $this->ownPicture,
'pictures' => $this->pictures,
'profiles' => $this->profiles,
'actions' => $this->threadActions[$thread->id],
'commentActions' => $this->commentActions,
'likes' => array(),
'flags' => $this->flags));
}
?></div>
