
#<?php if ($hasHeader):?>#<?php endif?> <?php echo $item['name']?>


<?php echo $item['description']?>


<?php if (!empty($item['methods'])):?>
<?php foreach ($item['methods'] as $method):?>
##<?php if ($hasHeader):?>#<?php endif?> <?php echo strtoupper($method['name'])?> /<?php echo strtolower($item['name'])?>


<?php if (!empty($method['response'])):?>
> This resource responds with:
>
<?php foreach ($method['response'] as $response):?>
<?php TemplateHelper::mdResponse($response)?>
<?php endforeach?>
<?php endif?>

<?php echo $method['description']?>


<?php if (!empty($method['requests'])):?>
| Parameter | Required | Max Length | Description |
| --------- | -------- | ---------- | ----------- |
<?php foreach ($method['requests'] as $request):?>
| <?php echo $request['name']?> | <?php echo $request['optional'] ? 'false': 'true'?> | <?php echo $request['length']['max']?> | <?php echo $request['description']?> |
<?php endforeach?>
<?php endif?>

<?php endforeach?>
<?php endif?>

<?php if (!empty($item['contents'])):?>
| Code | Description |
| ---- | ----------- |
<?php foreach ($item['contents'] as $content):?>
| `<?php echo $content['name']?>` | <?php echo $content['description']?> |
<?php endforeach?>
</table>
<?php endif?>