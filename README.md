# TP7DPBO2526C2

## Janji
Saya Bentar Bintang Umeir dengan nim 2509865 mengerjakan TP7 dalam mata kuliah DPBO untuk 
keberkahanNya maka saya tidak melakukan kecurangan seperti yang telah dispesifikasikan. Aamiin

## 1. Tema Website

Sistem untuk mengelola peminjaman alat olahraga (bola futsal, raket badminton, bola basket, net voli, dan sebagainya) di lingkungan kampus.

Fitur utama:
- Mengelola data **alat olahraga** beserta stok yang tersedia.
- Mengelola data **peminjam**.
- Mencatat **peminjaman** dan **pengembalian** alat. Stok berkurang otomatis saat dipinjam dan bertambah kembali saat dikembalikan.

---

## 2. Penjelasan Database

Nama database: `db_sports_loan` (file: `database/db_sports_loan.sql`)

### Relasi antar tabel

```
equipment (1) ──────< loans >────── (1) borrowers
   id  <── equipment_id   borrower_id ──>  id
```

### Tabel `equipment` (data alat)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AUTO_INCREMENT | ID alat |
| name | VARCHAR(100) | Nama alat |
| category | VARCHAR(50) | Kategori olahraga |
| stock | INT | Stok yang tersedia saat ini |

### Tabel `borrowers` (data peminjam)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AUTO_INCREMENT | ID peminjam |
| name | VARCHAR(100) | Nama peminjam |
| nim | VARCHAR(20), UNIQUE | NIM (tidak boleh kembar) |
| phone | VARCHAR(15) | Nomor HP |

### Tabel `loans` (transaksi peminjaman)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AUTO_INCREMENT | ID peminjaman |
| equipment_id | INT, **FK** -> equipment.id | Alat yang dipinjam |
| borrower_id | INT, **FK** -> borrowers.id | Peminjam |
| quantity | INT | Jumlah alat yang dipinjam |
| loan_date | DATE | Tanggal pinjam |
| due_date | DATE | Batas tanggal kembali |
| return_date | DATE, NULL | Tanggal alat dikembalikan |
| status | ENUM('borrowed','returned') | Status peminjaman |

---

## 3. Struktur Folder

```
sports-loan/
├── class/
│   ├── Equipment.php    # class untuk tabel equipment
│   ├── Borrower.php     # class untuk tabel borrowers
│   └── Loan.php         # class untuk tabel loans
├── config/
│   └── db.php           # class Database (koneksi PDO)
├── database/
│   └── db_sports_loan.sql
├── view/
│   ├── header.php       # header, navigasi, pesan sukses/error
│   ├── footer.php
│   ├── equipment.php    # halaman alat
│   ├── borrowers.php    # halaman peminjam
│   └── loans.php        # halaman peminjaman
├── index.php            # titik masuk, routing, dan pemrosesan aksi
├── style.css
└── README.md
```

---

## 4. Penjelasan Flow Kode

### Alur umum
1. Browser membuka `index.php?page=equipment|borrowers|loans`.
2. `index.php` memuat `config/db.php` dan semua class, membuat koneksi `Database`, lalu membuat objek `Equipment`, `Borrower`, dan `Loan`.
3. Jika request berupa **POST** (submit form), `index.php` membaca field `action`, memvalidasi input, lalu memanggil method class yang sesuai. Setelah berhasil, halaman di-redirect (Post-Redirect-Get) agar form tidak terkirim ulang saat di-refresh.
4. Jika request berupa **GET**, `index.php` memuat `header.php`, view sesuai halaman, lalu `footer.php`.
5. View memanggil method class (misalnya `$equipment->getAllEquipment()`) untuk menampilkan data. View tidak berisi query SQL.
6. Class berkomunikasi dengan MySQL lewat PDO, dan **semua query memakai `prepare()` + `execute()`**.

```
Browser -> index.php -> class/ (Equipment, Borrower, Loan) -> MySQL (db_sports_loan)
              |                       |
              └──── view/ <───────────┘  (data dikirim kembali sebagai array)
```

### Method pada tiap class

| Class | Create | Read | Update | Delete |
|---|---|---|---|---|
| `Equipment` | `createEquipment` | `getAllEquipment`, `getAvailableEquipment`, `getEquipmentById` | `updateEquipment` | `deleteEquipment` |
| `Borrower` | `createBorrower` | `getAllBorrowers`, `getBorrowerById` | `updateBorrower` | `deleteBorrower` |
| `Loan` | `createLoan` | `getAllLoans`, `getLoanById` | `updateLoan` | `deleteLoan` |

Tambahan di `Loan`: `returnLoan` untuk proses pengembalian alat.

### Logika khusus
- **Pinjam alat (`createLoan`)**: dijalankan dalam satu transaksi database. Sistem mengecek stok (`SELECT ... FOR UPDATE`), menyimpan data peminjaman, lalu mengurangi stok. Jika stok tidak cukup, peminjaman ditolak dan semua perubahan dibatalkan (`rollBack`).
- **Kembalikan alat (`returnLoan`)**: status menjadi `returned`, `return_date` diisi tanggal hari ini, dan stok bertambah sebanyak jumlah yang dipinjam. Pengembalian ganda ditolak.
- **Update peminjaman (`updateLoan`)**: hanya peminjam dan batas kembali yang dapat diubah, agar stok tetap konsisten.
- **Hapus peminjaman (`deleteLoan`)**: jika statusnya masih `borrowed`, stok dikembalikan terlebih dahulu sebelum data dihapus.
- **Hapus alat / peminjam**: jika masih dipakai pada tabel `loans`, penghapusan ditolak oleh foreign key dan method mengembalikan `false`, lalu halaman menampilkan pesan error.

### Keamanan
- Seluruh query memakai **prepared statement** (tidak ada query mentah).
- Output di view di-escape dengan `htmlspecialchars` (fungsi `e()`) untuk mencegah XSS.
- Parameter `page` divalidasi dengan whitelist sebelum dipakai pada `include`.

---

## 5. Dokumentasi 

# halaman equipment atau alat
<img width="959" height="532" alt="image" src="https://github.com/user-attachments/assets/0a15b9f4-2789-4be3-89ad-0e90e882019f" />

# halaman borrowers atau peminjam
<img width="958" height="526" alt="image" src="https://github.com/user-attachments/assets/3d8d8458-df18-446c-9ea3-c3df2b0e43e7" />

# halaman loans atau peminjaman
<img width="959" height="531" alt="image" src="https://github.com/user-attachments/assets/ddce69bc-8809-4b93-8a26-d418e96e8826" />

# CRUD di tabel alat 
https://github.com/user-attachments/assets/5f5b0ed3-846a-45f6-bfdc-b8c74c7ddc6b

# CRUD di tabel peminjam
https://github.com/user-attachments/assets/336aca1e-0ffb-409a-b355-27750ca99953

# CRUD di tabel peminjaman
https://github.com/user-attachments/assets/de2ad6a0-b161-48ae-a62a-61fc39c40992

