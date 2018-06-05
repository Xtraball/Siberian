<?php
// Clean-up old cron jobs!
Siberian_Feature::removeCronjob('statistics');
Siberian_Feature::removeCronjob('androidtools');
Siberian_Feature::removeCronjob('cachebuilder');
Siberian_Feature::removeCronjob('quotawatcher');