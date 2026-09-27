import base64
import re
import logging
from tonutils.client import TonapiClient
from tonutils.wallet import WalletV4R2
from config import API_TON, MNEMONIC

def fix_base64_padding(b64_string: str) -> str:
    missing_padding = len(b64_string) % 4
    if missing_padding:
        b64_string += '=' * (4 - missing_padding)
    return b64_string

class TonTransaction:
    async def send_ton_transaction(self, recipient, amount_nano, la, action, amount):
        client = TonapiClient(api_key=API_TON, is_testnet=False)
        wallet, public_key, private_key, mnemonic = WalletV4R2.from_mnemonic(client, MNEMONIC)
        logging.info("Кошелек успешно загружен.")

        if not recipient:
            logging.error("Ошибка: не указан получатель.")
            return
        if amount_nano <= 0:
            logging.error("Ошибка: некорректная сумма (должна быть больше 0).")
            return

        decoded_bytes = base64.b64decode(fix_base64_padding(la))
        decoded_text = ''.join(chr(b) if 32 <= b < 127 else ' ' for b in decoded_bytes)
        clean_text = re.sub(r'\s+', ' ', decoded_text).strip()

        if action == "stars":
            match = re.search(rf"{amount} Telegram Stars.*", clean_text)
            final_text = match.group(0) if match else clean_text
        else:
            match = re.search(r'(Telegram.*?Ref\s*#\S+)', clean_text)
            final_text = match.group(1).replace('Ref #', 'Ref#') if match else clean_text

        print(final_text)
        logging.info(f"Формируем текст для транзакции: {final_text}")

        tx_hash = await wallet.transfer(
            destination=recipient,
            amount=amount_nano,
            body=final_text,
        )
        logging.info(f"✅ Транзакция отправлена: {tx_hash}")
