<?php
if(!empty($this->redisInactive)):
?><div class="notifyWarn">Redis is currently inactive</div><?php
else:
?><h2>Redis Server</h2>
<table class="mod">
<tr>
<td>Redis Version</td>
<td><?php echo $this->redisInfo['redis_version'] ?></td>
</tr>
<tr>
<td>Used Memory</td>
<td><?php echo $this->redisInfo['used_memory_human'] ?></td>
</tr>
<tr>
<td>Memory Peak</td>
<td><?php echo $this->redisInfo['used_memory_peak_human'] ?></td>
</tr>
<tr>
<td>Available System Memory</td>
<td><?php echo $this->redisInfo['total_system_memory_human'] ?></td>
</tr>
<tr>
<td>Server Keys</td>
<td><?php echo $this->redisInfo['db0']['keys'] ?></td>
</tr>
<tr>
<td>Server Expires</td>
<td><?php echo $this->redisInfo['db0']['expires'] ?></td>
</tr>
</table>
<!--<?php var_export($this->redisInfo) ?>--> <?php
endif;
?><h2>Server Infos</h2>
<table class="mod">
<tr>
<td>Processes Running</td>
<td><?php echo $this->procStat['procs_running'] ?></td>
</tr>
<tr>
<td>Processes Blocked</td>
<td><?php echo $this->procStat['procs_blocked'] ?></td>
</tr>
<tr>
<td>System Load Average (1 Minute)</td>
<td><?php echo $this->sysloadavg[0] ?></td>
</tr>
<tr>
<td>System Load Average (5 Minute)</td>
<td><?php echo $this->sysloadavg[1] ?></td>
</tr>
<tr>
<td>System Load Average (15 Minute)</td>
<td><?php echo $this->sysloadavg[2] ?></td>
</tr>
<tr>
<td>Memory Free</td>
<td><?php echo $this->formatBytes($this->procMeminfo['MemFree']) ?></td>
</tr>
<tr>
<td>Memory Available</td>
<td><?php echo $this->formatBytes($this->procMeminfo['MemAvailable']) ?></td>
</tr>
<tr>
<td>Memory Total</td>
<td><?php echo $this->formatBytes($this->procMeminfo['MemTotal']) ?></td>
</tr>
</table>
<!-- <?php var_export($this->procStat) ?>-->
<!-- <?php var_export($this->procMeminfo) ?>--> <?php
?><h2>Network Infos</h2>
<table class="mod"><?php
foreach ($this->networkBytes as $networkDevice => $networkBytes):
?><tr>
<td><strong><?php print $networkDevice ?></strong> Received</td>
<td><?php echo $this->formatBytes($networkBytes['rx']) ?></td>
</tr>
<tr>
<td><strong><?php print $networkDevice ?></strong> Sent</td>
<td><?php echo $this->formatBytes($networkBytes['tx']) ?></td>
</tr><?php
endforeach;
?></table>
<!-- <?php var_export($this->networkBytes) ?>--> <?php
if (!empty($this->filesystem)):
?><h2>Filesystem</h2>
<table class="mod">
<tr>
<th>Mounted on</th>
<th>Used</th>
<th>Available</th>
<th>Capacity</th>
</tr><?php
foreach ($this->filesystem as $mount => $values):
?><tr>
<td><strong><?php print $mount ?></strong></td>
<td><?php echo $this->formatBytes($values['used']) ?></td>
<td><?php echo $this->formatBytes($values['available']) ?></td>
<td><?php echo $values['capacity'] ?></td>
</tr><?php
endforeach;
?></table>
<!-- <?php var_export($this->filesystem) ?>--> <?php
endif;
