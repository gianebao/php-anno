
#<?php if ($hasHeader):?>#<?php endif?> <?php echo $item['name']?>


<?php echo $item['description']?>


<?php if (!empty($item['methods'])):?>
<?php foreach ($item['methods'] as $method):?>
<?php if (false !== strpos($method['name'], ' ')):?>
##<?php if ($hasHeader):?>#<?php endif?> <?php echo $method['name']?>
<?php else:?>
##<?php if ($hasHeader):?>#<?php endif?> <?php echo strtoupper($method['name'])?> /<?php echo strtolower($item['name'])?>
<?php endif?>

<?php echo $method['description']?>

<?php if (!empty($method['response'])):?>
> This resource responds with:
>
<?php foreach ($method['response'] as $response):?>
<?php TemplateHelper::mdResponse($response)?>
<?php endforeach?>
<?php endif?>

<?php if (!empty($method['requests'])):?>
| Parameter | Required | Max Length | Description |
| --------- | -------- | ---------- | ----------- |
<?php foreach ($method['requests'] as $request):?>
| `<?php echo $request['name']?>` | <?php echo $request['optional'] ? '`no`': '`yes`'?> | <?php echo $request['length']['max']?> | <?php echo $request['description']?> |
<?php endforeach?>
<?php endif?>

<?php endforeach?>
<?php endif?>

<?php if (!empty($item['contents'])):?>
<?php foreach ($item['contents'] as $content):?>
##<?php if ($hasHeader):?>#<?php endif?> <?php echo $content['name']?>


<a href="#<?php echo TemplateHelper::permaName($content['name'])?>">[permalink]</a>


<?php echo $content['description']?>

<?php endforeach?>
<?php endif?>