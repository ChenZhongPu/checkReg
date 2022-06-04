# CheckReg
It can check whether a given email or phone number has been registered in websites.

## Install

```shell
pip install checkreg-zhongpu
```

## Supported Websites

### [dangdang](http://www.dangdang.com/)

```python
from checkreg import dangdang
x = dangdang.check_phone('138xxxxxxxx')
```

The result `x` is a dictionary. For example,

```python
{'statusCode': '0', 'errorCode': '0', 'errorMsg': None, 'if_exist': True, 'cust_id': '744073637'}
```

## License
MIT.