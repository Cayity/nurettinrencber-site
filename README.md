# Nurettin Rençber — WordPress teması

nurettinrencber.com.tr için yazılmış özel WordPress teması (`rencber/`) ve Vercel'de yayınlanan statik önizlemesi (`preview/`).

## Özellikler

- **Arka plan videolu açılış:** YouTube videosu, yüklenen MP4 ya da fotoğraf. Durdurma düğmesi var, "hareketi azalt" ayarına uyar.
- **Konserler** (sol menüde *Konserler*):
  - Her konser için tarih, saat, şehir, mekân, adres, bilet linki, fiyat, durum ve "tükendi" bilgisi girilir.
  - Geçmiş konserler kendiliğinden gizlenir.
  - Her konser için schema.org **MusicEvent** verisi üretilir. Google'daki "Etkinlikler" kutusu bu veriyi okur.
- **Dinle:** Spotify, Apple Music, YouTube (+ YouTube Music, Deezer) bağlantıları ve gömülü Spotify oynatıcısı.
- **Diskografi** (sol menüde *Albümler*):
  - 11 stüdyo albümü (şarkı listeleriyle), single'lar ve Home Concert serisi.
  - Veriler Apple Music kataloğundan derlendi: `rencber/inc/yayinlar.json`.
- **Videolar:** YouTube kanalındaki en yeni videolar otomatik gelir (API anahtarı gerekmez). İstenirse elle de liste girilebilir.
- **Galeri** (ışık kutulu), **Hakkında**, **İletişim** (Contact Form 7 desteği).
- **SEO:**
  - Sanatçı için Person + MusicGroup şeması; `sameAs` alanında Spotify / Apple Music / YouTube / Vikipedi profilleri yer alır.
  - Albümler için MusicAlbum şeması, Open Graph ve canonical etiketleri.
  - Rank Math / Yoast kuruluysa meta etiketlerini o eklentiye bırakır.
- Eski sitenin adresleri (`/hakkimda/`, `/albumlerim/`, `/galeri/`, `/iletisim/`) aynen çalışmaya devam eder.

Her şey **Görünüm › Özelleştir › Rençber Site Ayarları** panelinden değiştirilir.

## Kurulum

1. `dist/rencber.zip` dosyasını kullanın (WordPress'e geri dönülürse).
2. WordPress › Görünüm › Temalar › Yeni ekle › **Tema yükle** › `rencber.zip` › Etkinleştir.
3. Tema ilk açılışta şunları kendiliğinden aktarır:
   - eski sitedeki albüm kapaklarını, galeriyi ve biyografi fotoğrafını,
   - tüm diskografiyi,
   - Google'da görünen konserleri.

   Aktarım daha sonra tekrar çalıştırılmak istenirse: Görünüm › **Rençber kurulum**.
4. Ayarlar › Kalıcı bağlantılar › **Kaydet**. Bu adım `/konserler/` ve `/albumler/` adreslerinin çalışması için bir kez yapılır.
5. Enfold temasını hemen silmeyin; birkaç gün yedek olarak dursun.

## Yayındaki site (Vercel) ve yönetim paneli

Canlı site `data/site.json` dosyasından üretilir (`tools/build.mjs`, Vercel her commit'te çalıştırır).

- **Yönetim paneli:** `https://www.nurettinrencber.com.tr/admin`
  - Panelde konserler, diskografi, açılış videosu, metinler, Hakkında, galeri, videolar, bağlantılar, iletişim ve SEO düzenlenir.
  - "Kaydet ve yayınla" değişikliği GitHub'a commit eder; Vercel yaklaşık 1 dakikada yayınlar.
  - Fotoğraflar tarayıcıda küçültülüp `preview/uploads/` altına kaydedilir.
- **Günlük yenileme:** Vercel Cron her gece `/api/cron`'u çağırır. Tarihi geçen konserler düşer, YouTube videoları güncellenir.
- **Gerekli Vercel ortam değişkenleri:**
  - `ADMIN_PASSWORD`: panel şifresi, en az 10 karakter.
  - `GITHUB_TOKEN`: yalnızca bu depoya *Contents: Read and write* yetkili fine-grained token.

Yerelde deneme:

```bash
ADMIN_PASSWORD=bir-test-sifresi node tools/dev-server.mjs
```

Bu komut `http://localhost:5174/admin` adresinde çalışır; GitHub yerine yerel dosyalara yazar.
