# Loyiha papkasiga o‘tish
cd musurmonov-begzod.uz/Fragment/BuyStars/
# Yangi venv yaratish
$HOME/python-3.11/bin/python3.11 -m venv venv
# venv ni yoqish
source venv/bin/activate
# Kutubxonalarni o‘rnatish
pip install --upgrade pip
pip install httpx "tonutils<2.0"
# Tekshirish
python --version
pip list

python main.py