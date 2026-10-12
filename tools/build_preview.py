"""
Statik önizleme üretir (Vercel'de yayınlanan sürüm).

WordPress temasının ana sayfasıyla aynı HTML yapısını ve aynı style.css / main.js
dosyalarını kullanır; veriyi temanın varsayılanlarından ve inc/yayinlar.json'dan alır.
Görselleri eski siteden / Apple'dan indirip küçültür, temayı rencber.zip olarak paketler.

Kullanım:  python tools/build_preview.py
"""

import html
import io
import json
import os
import shutil
import sys
import urllib.request
import xml.etree.ElementTree as ET
import zipfile
from datetime import date

from PIL import Image

ROOT = os.path.relpath(os.path.join(os.path.dirname(__file__), ".."))  # göreli: Windows yol kodlaması sorunlarını önler
THEME = os.path.join(ROOT, "rencber")
OUT = os.path.join(ROOT, "preview")
IMG = os.path.join(OUT, "img")
UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36"
SITE = "https://www.nurettinrencber.com.tr"
UP = SITE + "/wp-content/uploads/"

ARTIST = "Nurettin Rençber"
LINKS = {
    "spotify": ("Spotify", "https://open.spotify.com/artist/0eZircNzjxnRUtRBVOsth2"),
    "apple": ("Apple Music", "https://music.apple.com/tr/artist/nurettin-ren%C3%A7ber/256846459"),
    "youtube": ("YouTube", "https://www.youtube.com/channel/UCZI7HYnOxzdgq-bTqLHdUDQ"),
}
SOCIAL = {
    "instagram": ("Instagram", "https://www.instagram.com/nurettinrencber.official/"),
    "facebook": ("Facebook", "https://www.facebook.com/nurettinrencberresmi/"),
}
EVENTS = [
    ("2026-10-16", "20:00", "Mardin", "Cemal Yay Kongre ve Konferans Salonu", "1226", "Bubilet"),
    ("2026-12-11", "20:30", "Gebze", "Kocaeli Osman Hamdi Bey Kültür Merkezi", "1159", ""),
    ("2026-12-11", "20:30", "Darıca", "Adnan Menderes Kültür Merkezi", "", ""),
    ("2026-12-21", "20:30", "İstanbul", "Mall of İstanbul", "959", ""),
]
GALLERY = [
    "2026/01/r01.jpg", "2026/01/r05.jpeg", "2026/01/r06.jpeg",
    "2025/01/0001.jpeg", "2025/01/0002.jpg", "2025/01/0003.jpg", "2025/01/0004.jpg",
    "2025/01/0005.jpg", "2025/01/0006.jpg", "2025/01/0007.jpg", "2025/01/0008.jpg",
    "2025/01/0009.jpg", "2025/01/0010.jpg", "2025/01/0011.jpg", "2025/01/0012.jpg",
    "2025/01/n0022.jpeg", "2025/01/n0021.jpeg", "2025/01/n0020.jpeg",
]
BIO = [
    "Rençber, Adıyaman’dan göçen bir ailenin çocuğu olarak 1960’ta Mersin’de doğdu. Müzikle tanışması ilk olarak 1977’de bağlama ile oldu. Çocukluğunun çoğu Mersin’de geçti. Liseyi bitirdikten sonra babasının bekçiliğini yaptığı bez fabrikasında çalıştı. 1981’de girdiği Ankara Üniversitesi Dil ve Tarih-Coğrafya Fakültesi’nden 1985’te felsefe öğretmeni olarak mezun oldu.",
    "İlk öğretmenlik deneyimini 1987’de Trabzon’un Tonya ilçesinde yaşadı. Dört sene burada öğretmenlik yaptıktan sonra Eskişehir, Çorum, Kırşehir ve Ankara’da görev yaptı. 2000 yılında öğretmenliği bıraktı.",
    "Nurettin Rençber müzik yaşamına öğretmenlik görevini sürdürürken başladı. İlk albümü Dağ Türküleri ve Deniz Şarkıları’nı 1991’de yayınladı.",
]
AYLAR = ["Oca", "Şub", "Mar", "Nis", "May", "Haz", "Tem", "Ağu", "Eyl", "Eki", "Kas", "Ara"]
AYLAR_UZUN = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"]
GUNLER = ["Pzt", "Sal", "Çar", "Per", "Cum", "Cmt", "Paz"]
GUNLER_UZUN = ["Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar"]

e = html.escape


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Referer": SITE + "/"})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read()


def save_img(url, name, size):
    """İndir, en uzun kenarı `size` olacak şekilde küçült, webp kaydet. Göreli yol döndürür."""
    rel = "img/" + name + ".webp"
    dest = os.path.join(OUT, rel)
    if not os.path.exists(dest):
        im = Image.open(io.BytesIO(fetch(url))).convert("RGB")
        im.thumbnail((size, size))
        im.save(dest, "WEBP", quality=80)
    return rel


def dims(rel):
    with Image.open(os.path.join(OUT, rel)) as im:
        return im.size


# --- PHP'deki rencber_icon() ile aynı yollar -------------------------------
def load_icons():
    src = open(os.path.join(THEME, "inc", "helpers.php"), encoding="utf-8").read()
    start = src.index("$paths = array(")
    end = src.index(");", start)
    icons = {}
    for line in src[start:end].splitlines():
        line = line.strip()
        if line.startswith("'") and "=>" in line:
            k, v = line.split("=>", 1)
            icons[k.strip().strip("'")] = v.strip().rstrip(",").strip("'")
    return icons


ICONS = load_icons()


def icon(name, size=20):
    return (f'<svg class="icon icon-{name}" width="{size}" height="{size}" viewBox="0 0 24 24" '
            f'fill="currentColor" aria-hidden="true" focusable="false">{ICONS[name]}</svg>')


def price(p):
    if not p:
        return ""
    return "₺" + f"{int(float(p)):,}".replace(",", ".")


def youtube_feed():
    xml = fetch("https://www.youtube.com/feeds/videos.xml?channel_id=UCZI7HYnOxzdgq-bTqLHdUDQ")
    ns = {"a": "http://www.w3.org/2005/Atom", "yt": "http://www.youtube.com/xml/schemas/2015"}
    root = ET.fromstring(xml)
    return [(en.find("yt:videoId", ns).text, en.find("a:title", ns).text) for en in root.findall("a:entry", ns)]


def section_head(eyebrow, title, link="", link_text=""):
    out = '<header class="section-head">'
    if eyebrow:
        out += f'<span class="eyebrow">{e(eyebrow)}</span>'
    out += f'<h2 class="section-title">{e(title)}</h2>'
    if link:
        out += f'<a class="section-link" href="{e(link)}">{e(link_text)} {icon("arrow", 16)}</a>'
    return out + "</header>"


def main():
    os.makedirs(IMG, exist_ok=True)
    disc = json.load(open(os.path.join(THEME, "inc", "yayinlar.json"), encoding="utf-8"))
    today = date.today().isoformat()

    # --- Görseller ---
    poster = save_img(UP + "2025/01/s001.jpg", "hero", 2000)
    about = save_img(UP + "2025/01/hakkimda.jpeg", "hakkimda", 1200)
    share = save_img(UP + "2025/01/0008.jpg", "paylasim", 1200)
    gallery = []
    for i, g in enumerate(GALLERY):
        try:
            gallery.append((save_img(UP + g, f"galeri-{i:02d}", 1000), save_img(UP + g, f"galeri-{i:02d}-buyuk", 2000)))
        except Exception as ex:  # noqa: BLE001
            print("galeri atlandı", g, ex)
    for i, a in enumerate(disc):
        src = UP + a["old_cover"] if a["type"] == "album" and a["old_cover"] else a["artwork"].replace("1000x1000bb", "600x600bb")
        a["cover"] = save_img(src, f"kapak-{i:02d}", 600) if src else ""

    # --- Bölümler ---
    events = [ev for ev in EVENTS if ev[0] >= today]
    rows = []
    for d, t, city, venue, p, vendor in events:
        y, m, dd = map(int, d.split("-"))
        wd = date(y, m, dd).weekday()
        buy = f'<span class="event-price"><b>{price(p)}</b> ile başlayan</span>' if p else ""
        rows.append(f'''
<li class="event">
	<time class="event-date" datetime="{d}T{t}"><span class="event-month">{AYLAR[m-1]}</span><span class="event-day">{dd}</span></time>
	<div class="event-info">
		<h3 class="event-city"><a href="#konserler">{e(city)}</a></h3>
		<p class="event-meta"><span>{GUNLER[wd]} {t}</span><span>{e(venue)}</span></p>
	</div>
	<div class="event-buy">{buy}<a class="btn btn-line btn-sm" href="#konserler">Detaylar</a></div>
</li>''')
    next_ev = events[0] if events else None

    def card(a):
        credit = f'<span class="album-credit">{e(a["credit"])} ile</span>' if a["credit"] else ""
        href = a["apple"] or LINKS["apple"][1]
        return f'''
<li class="album"><a href="{e(href)}" target="_blank" rel="noopener">
	<span class="album-cover"><img src="{a["cover"]}" alt="{e(a["title"])} kapak" loading="lazy" decoding="async" width="600" height="600"></span>
	<span class="album-year">{a["year"]}</span><span class="album-title">{e(a["title"])}</span>{credit}
</a></li>'''

    by_type = lambda t: sorted([a for a in disc if a["type"] == t], key=lambda a: a["date"], reverse=True)  # noqa: E731
    albums, singles, homes = by_type("album"), by_type("single"), by_type("home")
    home_rows = "".join(
        f'''<li><a href="{e(h["apple"])}" target="_blank" rel="noopener"><img src="{h["cover"]}" alt="" loading="lazy" width="56" height="56">
<span class="home-name">{e(h["title"].replace(" (Home Concert)", ""))}</span><span class="home-year">{AYLAR[int(h["date"][5:7])-1]} {h["year"]}</span></a></li>'''
        for h in homes)

    videos = youtube_feed()[:6]
    video_items = []
    for i, (vid, title) in enumerate(videos):
        thumb = f"https://i.ytimg.com/vi/{vid}/{'maxresdefault' if i == 0 else 'hqdefault'}.jpg"
        clean = title.replace(ARTIST, "").strip(" -")
        video_items.append(f'''
<li class="video{' is-featured' if i == 0 else ''}">
	<button class="video-lite" type="button" data-yt-lite="{vid}" aria-label="{e(title)}"><img src="{thumb}" alt="" loading="lazy" decoding="async"><span class="video-play">{icon("play", 28)}</span></button>
	<p class="video-title">{e(clean)}</p>
</li>''')

    gallery_items = []
    for small, big in gallery:
        w, h = dims(small)
        gallery_items.append(f'<li><a href="{big}" data-lightbox><img src="{small}" alt="{ARTIST}" loading="lazy" decoding="async" width="{w}" height="{h}"></a></li>')

    music_icons = "".join(f'<a href="{u}" target="_blank" rel="noopener" aria-label="{l}">{icon(k, 18)}</a>' for k, (l, u) in LINKS.items())
    hero_icons = "".join(f'<a href="{u}" target="_blank" rel="noopener" aria-label="{l}">{icon(k, 20)}</a>' for k, (l, u) in LINKS.items())
    platform_rows = "".join(f'''<li><a class="platform platform-{k}" href="{u}" target="_blank" rel="noopener">{icon(k, 24)}<span class="platform-name">{l}</span><span class="platform-go">{"İzle" if k == "youtube" else "Dinle"} {icon("arrow", 14)}</span></a></li>''' for k, (l, u) in LINKS.items())
    footer_links = "".join(f'<li><a href="{u}" target="_blank" rel="noopener me">{icon(k, 18)}<span>{l}</span></a></li>' for k, (l, u) in {**LINKS, **SOCIAL}.items())

    next_html = "<span></span>"
    if next_ev:
        y, m, dd = map(int, next_ev[0].split("-"))
        next_html = f'<a class="hero-next" href="#konserler"><span class="hero-next-label">Sıradaki konser</span><span class="hero-next-body"><b>{dd} {AYLAR[m-1]}</b> · {e(next_ev[2])} · {e(next_ev[3])}</span></a>'

    # --- schema.org (temadaki rencber_output_schema ile aynı yapı) ---
    artist = {
        "@type": ["Person", "MusicGroup"], "@id": "https://www.nurettinrencber.com.tr/#sanatci", "name": ARTIST,
        "url": "https://www.nurettinrencber.com.tr/", "genre": "Türk halk müziği", "jobTitle": "Müzisyen",
        "birthDate": "1960", "birthPlace": {"@type": "Place", "name": "Mersin"},
        "sameAs": [u for _, u in LINKS.values()] + [u for _, u in SOCIAL.values()] + ["https://tr.wikipedia.org/wiki/Nurettin_Ren%C3%A7ber"],
        "album": [{"@type": "MusicAlbum", "name": a["title"], "datePublished": str(a["year"])} for a in albums],
    }
    graph = [artist]
    for d, t, city, venue, p, vendor in events:
        ev = {
            "@type": "MusicEvent", "name": f"{ARTIST} {city} Konseri", "startDate": f"{d}T{t}:00+03:00",
            "eventStatus": "https://schema.org/EventScheduled", "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
            "location": {"@type": "Place", "name": venue, "address": {"@type": "PostalAddress", "addressLocality": city, "addressCountry": "TR"}},
            "performer": {"@type": ["Person", "MusicGroup"], "@id": artist["@id"], "name": ARTIST},
        }
        if p:
            ev["offers"] = {"@type": "Offer", "price": p, "priceCurrency": "TRY", "availability": "https://schema.org/InStock", "url": "https://www.nurettinrencber.com.tr/konserler/"}
        graph.append(ev)
    schema = json.dumps({"@context": "https://schema.org", "@graph": graph}, ensure_ascii=False)

    tpl = open(os.path.join(ROOT, "tools", "preview_template.html"), encoding="utf-8").read()
    page = (tpl
            .replace("{{SCHEMA}}", schema)
            .replace("{{MUSIC_ICONS}}", music_icons)
            .replace("{{HERO_ICONS}}", hero_icons)
            .replace("{{POSTER}}", poster)
            .replace("{{SHARE}}", share)
            .replace("{{NEXT}}", next_html)
            .replace("{{PAUSE_PLAY}}", icon("pause", 18) + icon("play", 18))
            .replace("{{PLAY16}}", icon("play", 16))
            .replace("{{EVENTS_HEAD}}", section_head("Takvim", "Konserler"))
            .replace("{{EVENTS}}", "".join(rows))
            .replace("{{LISTEN_HEAD}}", section_head("Dijital platformlar", "Dinle"))
            .replace("{{PLATFORMS}}", platform_rows)
            .replace("{{DISCO_HEAD}}", section_head(f"Diskografi · {len(disc)} yayın", "Albümler"))
            .replace("{{N_SINGLES}}", str(len(singles)))
            .replace("{{SINGLES}}", "".join(map(card, singles)))
            .replace("{{N_ALBUMS}}", str(len(albums)))
            .replace("{{ALBUMS}}", "".join(map(card, albums)))
            .replace("{{N_HOMES}}", str(len(homes)))
            .replace("{{HOMES}}", home_rows)
            .replace("{{ABOUT_IMG}}", about)
            .replace("{{ABOUT_HEAD}}", section_head("Biyografi", "Hakkında"))
            .replace("{{BIO}}", "".join(f"<p>{e(p)}</p>" for p in BIO))
            .replace("{{VIDEOS_HEAD}}", section_head("YouTube", "Videolar", LINKS["youtube"][1], "Kanala abone ol"))
            .replace("{{VIDEOS}}", "".join(video_items))
            .replace("{{GALLERY_HEAD}}", section_head("Fotoğraflar", "Galeri"))
            .replace("{{GALLERY}}", "".join(gallery_items))
            .replace("{{CONTACT_HEAD}}", section_head("Konser ve organizasyon", "İletişim"))
            .replace("{{PHONE_ICON}}", icon("phone", 18))
            .replace("{{MAIL_ICON}}", icon("mail", 18))
            .replace("{{FOOTER_LINKS}}", footer_links)
            .replace("{{YEAR}}", str(date.today().year)))
    open(os.path.join(OUT, "index.html"), "w", encoding="utf-8", newline="\n").write(page)

    shutil.copy(os.path.join(THEME, "style.css"), os.path.join(OUT, "style.css"))
    shutil.copy(os.path.join(THEME, "assets", "js", "main.js"), os.path.join(OUT, "main.js"))

    # Tema paketi (WordPress › Görünüm › Temalar › Yeni ekle › Tema yükle).
    with zipfile.ZipFile(os.path.join(OUT, "rencber.zip"), "w", zipfile.ZIP_DEFLATED) as z:
        for base, _, files in os.walk(THEME):
            for f in files:
                full = os.path.join(base, f)
                z.write(full, os.path.join("rencber", os.path.relpath(full, THEME)))
    print("önizleme hazır:", OUT, "| konser:", len(events), "| yayın:", len(disc), "| video:", len(videos), "| galeri:", len(gallery))


if __name__ == "__main__":
    sys.stdout.reconfigure(encoding="utf-8")
    main()
