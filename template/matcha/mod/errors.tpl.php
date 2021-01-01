<?php

if (!empty($this->errorDetails)):
    ?><h1>Error Details</h1><?php
    foreach ($this->errorDetails as $file => $errorDetail):
        ?><h2><?php echo prepareHTML($file) ?></h2><?php 
        echo prepareHTML($errorDetail);
    endforeach;
    
elseif (!empty($this->errors)):
    ?><h1>Errors (<?php echo $this->errorFile ?>)</h1>
    <table class="mod"><?php
    foreach ($this->errors as $errorKey => $error):
        ?><tr>
            <td>
                <a href="<?php echo $this->path ?>mod/errors/<?php echo $this->errorFile ?>/<?php echo $errorKey ?>"><?php 
                    echo prepareHTML($error['str']) 
                ?></a><br />
                <?php echo prepareHTML($error['file']) ?>
            </td>
            <td><?php 
                echo $error['count'] 
            ?></td>
        </tr><?php
    endforeach;
    ?></table><?php

elseif (!empty($this->errorFiles)):
    ?><h1>Errors</h1>
    <ul class="list"><?php
        foreach ($this->errorFiles as $errorFile => $count):
            ?><li>
                <a href="<?php echo $this->path ?>mod/errors/<?php echo $errorFile ?>/"><?php 
                    echo $errorFile 
                ?></a> (<?php echo $count ?>)
            </li><?php
        endforeach;
    ?></ul><?php
endif;
