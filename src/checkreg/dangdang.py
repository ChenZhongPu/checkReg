import datetime
import time
import requests
import hashlib
from urllib.parse import urlencode
from .aes import AESTool

BASE_URL = "https://login.dangdang.com"
P_PERMANENT_ID = 'permanent_id'
P_T = 't'
P_CT = 'ct'
P_STATUS_CODE = 'statusCode'
P_REQUEST_ID = 'requestId'
P_RANKEY = 'rankey'
P_SIGN = 'sign'
UTF8 = 'UTF-8'
AES_IV = '0102030405060708'


def permanent_id():
    now = datetime.datetime.now().strftime("%Y%m%d%H%M%S")
    return f'{now}00000000000'


def request_key(permanent):
    data = {P_PERMANENT_ID: permanent, P_T: int(time.time() * 1000)}
    ran_key_url = '/api/customer/loginapi/getRankey'
    x = requests.post(f'{BASE_URL}{ran_key_url}', data=data).json()
    assert x[P_STATUS_CODE] == '0', 'Error when getting random key!'
    return x[P_REQUEST_ID], x[P_RANKEY]


def check_phone(phone_number):
    permanent = permanent_id()
    request_id, random_key = request_key(permanent)

    data = {P_CT: "pc", "mobile_phone": phone_number,
            P_PERMANENT_ID: permanent,
            P_REQUEST_ID: request_id,
            P_T: int(time.time() * 1000)}
    data = {k: data[k] for k in sorted(data)}
    param = urlencode(data)
    md5 = hashlib.md5()
    md5.update(param.encode(UTF8))
    data_md5 = md5.hexdigest()
    aes_tool = AESTool(random_key, AES_IV)
    sign = aes_tool.aes_encrypt(data_md5)
    data[P_SIGN] = sign
    verify_phone = '/api/customer/loginapi/verifyMobilePhone'
    x = requests.post(f'{BASE_URL}{verify_phone}', data=data).json()
    assert x[P_STATUS_CODE] == '0', 'Error when verifying'
    return x
