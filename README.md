 RaketmanDatabasePartitionProcessor
==========================

This library provides a way to auto create|delete partition, which base of date field

!!!Work only for mysql partition!!!


```
/**
 *
 * 
 * @RaketmanDatePartition(
 *     table="analyzer_setting",
 *     type="month",
 *     id_field="id",
 *     date_field="created",
 *     safe_period="5",
 *     create_period="4",
 *     manual=true
 * )
 */

if you use doctrine/annotation add @IgnoreAnnotation("RaketmanDatePartition")

```


RaketmanDatePartition options list:

```
table - table name
type - type of partition (day|month|year)
id_field - name of id field
date_field -name of date field, which use in partition definition
safe_period - count of past period, than could,t be delete | safe_period=null - partition not be deleted
create_period - count of future period, than be created
manual - (true|false), if true, to process this need to use --table options in script
```


To process partition you need to run command.
This command add|delete partition based options in RaketmanDatePartition.
Need to work periodically, for example in cron.

```
php vendor/raketman/database-partition-processor/bin/process.php  --table=* --database-url=* --dirs=* --env-database-url=*

--dirs - dirs to scan RaketmanDatePartition (defaut src) (not required)
--database-url - url to connect, example mysql://db_user:db_password@127.0.0.1:3306/db_name (required one of url)
--env-database-url - env, who contain database-url (required one of url)
--table - table name to manual process (not required)

```

To create partition you need to run command.
This command create partition definition based options in RaketmanDatePartition.
Run only one time for each table, that you want partitionated.

```
create partition in manual mode, only one table for one run.

 php vendor/raketman/database-partition-processor/bin/create.php --entity-path=/var/www/app/src/Entity/AnalyzerSetting.php --database-url=mysql://root:root@ngynx-analyzer-mariadb:3306/gosuslugi?cha
rset=utf8

--entity-path - file to scan RaketmanDatePartition 
--database-url - url to connect, example mysql://db_user:db_password@127.0.0.1:3306/db_name (required one of url)
--env-database-url - env, who contain database-url (required one of url)

```