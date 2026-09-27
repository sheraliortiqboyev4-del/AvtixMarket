import asyncio
import sys
import logging
from client import FragmentClient
from transaction import TonTransaction

logging.basicConfig(level=logging.INFO, format="%(asctime)s - %(levelname)s - %(message)s")

async def main(action, query, amount):
    client = FragmentClient()

    recipient = await client.fetch_recipient(query, amount, action)
    if not recipient:
        logging.error("Ошибка: получатель не найден.")
        return

    req_id = await client.fetch_req_id(recipient, amount, action)
    if not req_id:
        logging.error("Ошибка: req_id не получен.")
        return

    recipient, amount_nano, la = await client.fetch_buy_link(recipient, req_id, amount, action)
    if not (recipient and amount_nano and la):
        logging.error("Ошибка: не удалось получить ссылку на оплату.")
        return

    amount_decimal = float(amount_nano) / 1_000_000_000
    logging.info(f"Сумма для отправки: {amount_decimal:.4f} TON")

    transaction = TonTransaction()
    await transaction.send_ton_transaction(recipient, amount_decimal, la, action, amount)


if __name__ == "__main__":
    if len(sys.argv) < 4:
        print("Xato: parametrlarni to'g'ri kiriting!")
        print("Misol (stars):   python main.py stars @username 50")
        print("Misol (premium): python main.py premium @username 3")
        sys.exit(1)

    action = sys.argv[1]          # stars yoki premium
    query  = sys.argv[2]          # @username
    amount = int(sys.argv[3])     # stars: 50,100... | premium: 1,3,6,12

    if action not in ("stars", "premium"):
        print("Xato: action faqat 'stars' yoki 'premium' bo'lishi mumkin!")
        sys.exit(1)

    asyncio.run(main(action, query, amount))
