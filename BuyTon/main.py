# main.py
import asyncio
import sys
import logging
from tonutils.client import TonapiClient
from tonutils.wallet import WalletV4R2
from config import API_TON, MNEMONIC

logging.basicConfig(
    level=logging.INFO,
    format='%(message)s'  
)

class TonSender:
    def __init__(self):
        self.client = TonapiClient(api_key=API_TON, is_testnet=False)

    async def send(self, recipient: str, amount_ton: float):
        if not recipient:
            print("❌ Xato: recipient parametri berilmagan")
            return False

        if amount_ton <= 0:
            print("❌ Xato: amount 0 dan katta bo'lishi kerak")
            return False

        try:
            wallet, _, _, _ = WalletV4R2.from_mnemonic(self.client, MNEMONIC)
            print(f"✅ Hamyon yuklandi | Sender: {wallet.address.to_str(is_bounceable=False)}")

            address_str = wallet.address.to_str(is_bounceable=False)
            balance_nano = await self.client.get_account_balance(address_str)
            balance_ton = balance_nano / 1_000_000_000

            print(f"💰 Balans: {balance_ton:.6f} TON")
            print(f"📤 Yuborilayotgan miqdor: {amount_ton:.6f} TON → {recipient}")

            if balance_ton < amount_ton + 0.05:
                print(f"❌ Yetarli mablag' yo'q! Kerak: {amount_ton + 0.05:.4f} TON")
                return False

            print("🚀 Tranzaksiya yuborilmoqda...")
            tx_hash = await wallet.transfer(
                destination=recipient,
                amount=amount_ton,
                body="@SoraPayBot bilan xaridingiz barakali bo'lsin"
            )

            print(f"✅ Транзакция отправлена: {tx_hash}")
            print(f"🔗 https://tonviewer.com/transaction/{tx_hash}")

            await asyncio.sleep(3)
            new_balance = await self.client.get_account_balance(address_str) / 1_000_000_000
            print(f"💰 Yangi balans: {new_balance:.6f} TON")

            return True

        except Exception as e:
            print(f"❌ Xato: {str(e)}")
            return False


async def main():
    if len(sys.argv) < 3:
        print("❌ Xato: recipient va amount parametrlarini berish kerak")
        print("Foydalanish: python main.py <recipient> <amount>")
        sys.exit(1)

    recipient = sys.argv[1].strip()

    try:
        amount = float(sys.argv[2].strip())
    except ValueError:
        print("❌ Xato: amount to'g'ri son bo'lishi kerak (masalan: 0.20)")
        sys.exit(1)

    sender = TonSender()
    success = await sender.send(recipient, amount)

    if success:
        print("STATUS: SUCCESS")
    else:
        print("STATUS: FAILED")


if __name__ == "__main__":
    asyncio.run(main())