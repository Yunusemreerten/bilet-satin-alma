# Bilet Satın Alma Platformu

Bu proje, modern web teknolojileri kullanılarak geliştirilmiş dinamik, veritabanı destekli ve çok kullanıcılı bir otobüs bileti satış platformudur. Proje, farklı kullanıcı rolleri (Ziyaretçi, Yolcu, Firma Admini, Süper Admin) için yetkilendirme sistemleri içermektedir.

---

##  Özellikler

Proje, 4 farklı kullanıcı rolü için özelleştirilmiş işlevler sunar:

### Ziyaretçi (Giriş Yapmamış Kullanıcı)
- Ana sayfada kalkış ve varış noktası seçerek sefer arayabilme.
- Sayfa ilk açıldığında tüm yaklaşan seferleri listeleyebilme.
- Sefer detaylarını görebilme.

### Kullanıcı (Yolcu)
- Sisteme kayıt olabilme ve giriş/çıkış yapabilme.
- Giriş yaptıktan sonra seferleri listeleyebilme ve bilet satın alabilme.
- Satın alma işlemini sanal bakiye üzerinden gerçekleştirme.
- Menüde güncel bakiyeyi anlık olarak görebilme.
- İndirim kuponu kullanabilme.
- Satın aldığı biletleri "Biletlerim" sayfasında listeleyebilme.
- Koşullar uygunsa (sefer saatine 1 saatten fazla varsa) bileti iptal edip para iadesi alabilme.
- Satın alınmış biletleri PDF formatında indirebilme.

### Firma Admini
- Sadece kendi firmasına ait seferleri yönetebilme (CRUD: Oluşturma, Okuma, Güncelleme, Silme).
- Yeni seferler (güzergah, tarih, saat, fiyat, kapasite vb.) oluşturabilme.
- Mevcut seferleri düzenleyebilme ve silebilme.

### Süper Admin
- Sistemdeki tüm otobüs firmalarını yönetebilme (CRUD).
- Yeni "Firma Admini" kullanıcıları oluşturabilme ve onları bir firmaya atayabilme.
- Mevcut Firma Adminlerini yönetebilme (CRUD).
- Tüm sistemde geçerli genel indirim kuponları oluşturabilme ve yönetebilme (CRUD).

---

## Kullanılan Teknolojiler

- **Backend:** PHP
- **Veritabanı:** SQLite
- **Frontend:** HTML, CSS, Bootstrap 5
- **Geliştirme Ortamı:** Docker (Apache + PHP)
- **PDF Üretimi:** tFPDF Kütüphanesi

---

##  Kurulum ve Çalıştırma

Bu projeyi çalıştırmak için bilgisayarınızda **Docker Desktop**'ın kurulu olması yeterlidir.

1.  Bu depoyu bilgisayarınıza klonlayın:
    ```bash
    git clone https://github.com/Yunusemreerten/bilet-satin-alma.git
    ```
2.  Terminali proje ana dizininde açın.
3.  Aşağıdaki komutu çalıştırarak Docker container'larını başlatın:
    ```bash
    docker-compose up -d
    ```
4.  Kurulum tamamlandıktan sonra, internet tarayıcınızdan **`http://localhost:8080`** adresine gidin.

Proje artık çalışır durumdadır.
