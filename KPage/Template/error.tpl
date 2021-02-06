<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <style>
        body { <?= is_debug() ? 'background-color: #FFF; color: #333' : 'background-color: #1d6ab8; color: #FFF'; ?>; }
        a { color: #FFF; }
        a:hover { color: #EEE; }

        table.trace {
          border-collapse: collapse;
        }
        table.trace, 
        table.trace caption, 
        table.trace th,
        table.trace td {
            border: 1px solid #666666;
        }
        table.trace caption {
          background-color: #8892bf;
          border-bottom: none;
          color: #FFF;
          padding: .5em;
          text-align: left;
        }
        table.trace th,
        table.trace td {
          padding: .25em;
        }
    </style>
  </head>
  <body>
<?php if(is_debug()) { $ex = get_last_exception(); ?>
    <h1>详细系统错误信息</h1>
    <p style="">
    [<?= $ex->getCode(); ?>]: 
    <?= $ex->getMessage(); ?> in the file <?= $ex->getFile(); ?> at line: <?= $ex->getLine(); ?>
    </p>
    <table class="trace">
      <caption>Tracing</caption>
      <thead>
        <tr>
          <th>行号</th>
          <th>类</th>
          <th>类型</th>
          <th>方法</th>
          <th>文件路径</th>
        </tr>
      </thead>
      <tbody>
<?php foreach($ex->getTrace() as $trace) { ?> 
        <tr>
          <td><?= $trace['line'] ?? ''; ?></td>
          <td><?= $trace['class'] ?? ''; ?></td>
          <td><?= $trace['type'] ?? ''; ?></td>
          <td><?= $trace['function'] ?? ''; ?></td>
          <td><?= $trace['file'] ?? ''; ?></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
<?php } else { ?>
    <h1 style="font-size:500%;">:(</h1>
    <h2>系统运行中遇到了故障......</h2>
    <p>
        系统执行过程中遇到了异常，如果您是开发人员，需要查看更多错误信息，请打开系统的debug模式！想 <a href="https://blog.kuakee.com" target="_blank">了解更多信息</a> ？
    </p>
    <p style="border-top: 1px solid #FFF; padding: 1em 0;">
      内核框架版本：<?= get_version(); ?>
    </p>
<?php } ?>
  </body>
</html>