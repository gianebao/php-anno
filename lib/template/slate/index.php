---
<?php
foreach ($item as $key => $value)
{
    if (is_bool($value))
    {
        echo $key . ': ' . ($value ? 'true': 'false');
    }
    elseif (is_array($value))
    {
        echo $key . ":\n  - " . implode("\n  - ", $value);
    }
    else
    {
        echo $key . ': ' . $value;
    }
    
    echo "\n\n";
}
?>
---