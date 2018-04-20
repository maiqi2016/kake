# 项目
喀客酒店预订平台

## 安装步骤

```shell
$ git clone https://github.com/maiqi2016/kake.git
$ chmod a+x kake/install.sh
```

### 本机环境

```shell
$ cd kake
$ composer install
$ ./install.sh
```

### `Docker` 环境

```
$ sudo docker-compose up -d     # 并确保已经安装 `/web/docker` 并执行了 `/web/docker/script/` 目录下的所有脚本
$ mq-composer install --ignore-platform-reqs
$ mq-bash kake/install.sh
```