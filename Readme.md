# WhatsApp Business API Module for PrestaShop

[![Version](https://img.shields.io/badge/version-4.0.1-blue.svg)](https://github.com/luqequax1a-new/whatsapp-api)
[![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7.x%20%7C%208.x-orange.svg)](https://www.prestashop.com/)
[![License](https://img.shields.io/badge/license-AFL%203.0-green.svg)](https://opensource.org/licenses/AFL-3.0)

## 📱 Genel Bakış

WhatsApp Business API Module, PrestaShop e-ticaret mağazanız için güçlü bir WhatsApp entegrasyon çözümüdür. Bu modül, müşterilerinizle WhatsApp üzerinden otomatik iletişim kurmanızı sağlar ve sipariş süreçlerinizi optimize eder.

## ✨ Özellikler

### 🔔 Otomatik Bildirimler
- **Sipariş Onayı**: Yeni siparişler için otomatik onay mesajları
- **Sipariş Güncelleme**: Sipariş durumu değişikliklerinde bildirimler
- **Kargo Takibi**: Kargo takip numarası güncellemelerinde otomatik mesajlar
- **OTP Doğrulama**: Mobil numara doğrulama için tek kullanımlık şifre gönderimi

### 📢 Kampanya Yönetimi
- Çoklu kampanya oluşturma ve yönetimi
- Tüm müşterilere toplu mesaj gönderimi
- Özelleştirilebilir mesaj şablonları
- Medya destekli kampanyalar (resim, video)

### 🎯 Müşteri Tercihleri
- Müşterilerin bildirim alma/reddetme seçenekleri
- GDPR uyumlu veri yönetimi
- Kişiselleştirilmiş bildirim ayarları

### 🔧 Gelişmiş Özellikler
- Facebook Graph API v17.0 desteği
- Çoklu dil desteği (10+ dil)
- Webhook entegrasyonu
- Detaylı analitik ve raporlama
- Responsive admin paneli

## 🚀 Kurulum

### Gereksinimler
- PrestaShop 1.7.x.x veya 8.x.x
- PHP 7.1 veya üzeri
- WhatsApp Business API erişimi
- Facebook Developer hesabı

### Kurulum Adımları

1. **Modülü İndirin**
   ```bash
   git clone https://github.com/luqequax1a-new/whatsapp-api.git
   ```

2. **PrestaShop'a Yükleyin**
   - Modül dosyalarını `/modules/wkwhatsappbusiness/` klasörüne kopyalayın
   - PrestaShop admin panelinden modülü etkinleştirin

3. **API Yapılandırması**
   - WhatsApp Business API kimlik bilgilerinizi girin
   - Webhook URL'sini yapılandırın
   - Mesaj şablonlarınızı oluşturun

## ⚙️ Yapılandırma

### API Ayarları
- **Phone Number ID**: WhatsApp Business telefon numarası ID'si
- **Access Token**: Facebook Graph API erişim token'ı
- **Webhook URL**: Gelen mesajlar için webhook adresi
- **Verify Token**: Webhook doğrulama token'ı

### Mesaj Şablonları
Modül aşağıdaki varsayılan şablonları içerir:
- `wab_order_create`: Sipariş oluşturma bildirimi
- `wab_order_update`: Sipariş güncelleme bildirimi
- `wab_order_track`: Kargo takip bildirimi
- `wab_verify_otp`: OTP doğrulama mesajı

## 📊 Kullanım

### Kampanya Oluşturma
1. Admin panelinden "WhatsApp Business" modülüne gidin
2. "Kampanyalar" sekmesini seçin
3. "Yeni Kampanya" butonuna tıklayın
4. Kampanya detaylarını doldurun:
   - Kampanya adı
   - Mesaj içeriği
   - Medya dosyaları (opsiyonel)
   - Buton ayarları
5. Kampanyayı kaydedin ve gönderin

### Otomatik Bildirimler
Modül aşağıdaki PrestaShop hook'larını kullanır:
- `actionValidateOrder`: Sipariş onayında
- `actionOrderStatusPostUpdate`: Sipariş durumu güncellemesinde
- `actionAdminOrdersTrackingNumberUpdate`: Kargo takip numarası güncellemesinde

## 🔧 Geliştirici Bilgileri

### Dosya Yapısı
```
wkwhatsappbusiness/
├── classes/                 # Ana sınıflar
│   ├── WkWABCampaign.php   # Kampanya yönetimi
│   ├── WkWABCustomer.php   # Müşteri yönetimi
│   ├── WkWABHelper.php     # Yardımcı fonksiyonlar
│   └── WkWABWebhook.php    # Webhook işlemleri
├── controllers/             # Kontrolcüler
│   ├── admin/              # Admin kontrolcüleri
│   └── front/              # Frontend kontrolcüleri
├── libs/                   # Kütüphaneler
│   ├── WkWhatsAppMessage.php
│   └── WkWhatsAppTemplate.php
├── views/                  # Görünüm dosyaları
│   ├── templates/
│   ├── css/
│   └── js/
└── translations/           # Çeviri dosyaları
```

### API Entegrasyonu
Modül Facebook Graph API v17.0 kullanır:
```php
// Basit metin mesajı gönderme
$message = new WkWhatsAppMessage();
$message->sendSimpleTxtMessage($phoneNumber, $textMessage);

// Şablon mesajı gönderme
$template = new WkWhatsAppTemplate();
$template->sendTemplateMessage($phoneNumber, $templateName, $parameters);
```

## 🌍 Çoklu Dil Desteği

Modül aşağıdaki dilleri destekler:
- 🇹🇷 Türkçe
- 🇺🇸 İngilizce
- 🇩🇪 Almanca
- 🇫🇷 Fransızca
- 🇪🇸 İspanyolca
- 🇮🇹 İtalyanca
- 🇳🇱 Hollandaca
- 🇵🇱 Lehçe
- 🇵🇹 Portekizce
- 🇷🇺 Rusça
- 🇬🇷 Yunanca
- 🇭🇺 Macarca

## 📈 Sürüm Geçmişi

### v4.0.1 (Güncel)
- ✅ PrestaShop 8.x.x uyumluluğu
- ✅ Geliştirilmiş modül performansı
- ✅ Şablonlarda örnek değerler eklendi
- ✅ Yeni kategorilere göre şablonlar oluşturuldu
- ✅ Facebook Graph API v17.0 uyumluluğu
- ✅ Yapılandırma sayfasında API hata mesajları
- 🐛 Şablon dil ISO kodu sorunu düzeltildi

### v4.0.0
- 🎉 İlk sürüm
- 📱 Sipariş bildirimleri
- 🔔 OTP doğrulama
- 📢 Kampanya yönetimi
- 🌐 Çoklu dil desteği

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'inizi push edin (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje [Academic Free License 3.0](https://opensource.org/licenses/AFL-3.0) altında lisanslanmıştır.

## 🆘 Destek

- 📧 E-posta: [destek@example.com](mailto:destek@example.com)
- 📖 Dokümantasyon: [Wiki sayfası](https://github.com/luqequax1a-new/whatsapp-api/wiki)
- 🐛 Hata bildirimi: [Issues](https://github.com/luqequax1a-new/whatsapp-api/issues)

## 👨‍💻 Geliştirici

**Webkul Software Pvt. Ltd.**
- Website: [https://webkul.com](https://webkul.com)
- GitHub: [@webkul](https://github.com/webkul)

---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!

[![GitHub stars](https://img.shields.io/github/stars/luqequax1a-new/whatsapp-api.svg?style=social&label=Star)](https://github.com/luqequax1a-new/whatsapp-api/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/luqequax1a-new/whatsapp-api.svg?style=social&label=Fork)](https://github.com/luqequax1a-new/whatsapp-api/network/members)
[![GitHub watchers](https://img.shields.io/github/watchers/luqequax1a-new/whatsapp-api.svg?style=social&label=Watch)](https://github.com/luqequax1a-new/whatsapp-api/watchers)