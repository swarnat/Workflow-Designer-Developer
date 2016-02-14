# Add functions you could use in custom expression

#### This is the only interface existing also in the 5.4.0 version.

Sometimes you want to integrate a function which will too complexe or big to integrate in a simple text field. 
For this you should create your own PHP functions.

**VtigerCRM 5.x**: Create a new file inside modules/Workflow2/functions/ with the filename <individual>.inc.php  
**VtigerCRM 6.x**: Create a new file inside modules/Workflow2/extends/functions/ with the filename <individual>.inc.php

All functions with the nameprefix **wf_** you define in this file, will be available during custom expressions without any further modifications.

**Don't modify the core.inc.php File.** There are the functions defined, which already come with Workflow Designer and will be overwritten with every update.
But you could use this file as example, how your functions should be build.

##### If you break the PHP Syntax and integrate an Error in this file, no Workflow could be executed, because this files are included in most tasks!

*Example, which is currently integrated in Core:*

```php
if(!function_exists("wf_date")) {
    function wf_date($value, $interval, $format = "Y-m-d") {
        if(empty($interval)) {
            $dateValue = strtotime($value);
        } else {
            $dateValue = strtotime($interval, strtotime($value));
        }
 
        return date($format, $dateValue);
    }
}
```

See here: https://github.com/swarnat/Workflow-Designer-Developer/blob/master/extends/functions/core.inc.php