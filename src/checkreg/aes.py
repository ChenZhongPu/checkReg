from Crypto.Cipher import AES
import base64
UTF8 = 'UTF-8'


class AESTool:
    def __init__(self, key, iv):
        self.key = key.encode(UTF8)
        self.iv = iv.encode(UTF8)

    def pkcs7padding(self, text):
        bs = 16
        length = len(text)
        bytes_length = len(text.encode(UTF8))
        padding_size = length if (bytes_length == length) else bytes_length
        padding = bs - padding_size % bs
        padding_text = chr(padding) * padding
        return text + padding_text

    def aes_encrypt(self, content):
        cipher = AES.new(self.key, AES.MODE_CBC, self.iv)
        content_padding = self.pkcs7padding(content)
        encrypt_bytes = cipher.encrypt(content_padding.encode(UTF8))
        result = str(base64.b64encode(encrypt_bytes), encoding=UTF8)
        return result

    def aes_decrypt(self, content):
        cipher = AES.new(self.key, AES.MODE_CBC, self.iv)
        content = base64.b64decode(content)
        text = cipher.decrypt(content).decode(UTF8)
        return self.pkcs7padding(text)
