# think-mysqldump
ThinkPHP 数据库导出组件

## 安装
```
composer require xiaodi/think-mysqldump:dev-master
```

## 使用
```php
use xiaodi\Mysqldump\Facade\Mysqldump;

// 默认导出所有表
Mysqldump::connect()->start();
```

## Api

方法 | 说明 | 默认 |
:-: | :-: | :-: | 
connect() | 连接数据库 | - |
table() | 单独导出某个表 | - |
dropTableIfExists() | 是否如果存在则删除表| true |
includeTableStructure() | 是否导出表结构 | true |
inlucdeTableContent() | 是否导出表内容 | true |