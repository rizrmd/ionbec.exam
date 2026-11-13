# Fix report: soal ujian tampil tanpa teks/opsi (exam.ionbec.com)

Branch: `fix/exam-missing-question` · Diverifikasi 2026-11-13 di staging lokal (bukan produksi).
Status: **siap direview**. Deploy produksi belum dilakukan dan menunggu persetujuan pemilik repo.

## 1. Akar masalah

1. Delivery menyimpan *snapshot* daftar item saat dijadwalkan. Halaman peserta menerima item snapshot
   tanpa isi soal, lalu memanggil `GET /exam/questions/{item_hash}`. Endpoint mencari item lewat kolom
   `items.hash`. Kolom ini tidak ditulis ulang oleh model Item, dan nilai lamanya dibuat dengan key lain.
   Akibatnya item seperti **TO OTD 230526-04** (id 936, `items.hash` NULL, hash snapshot `x5gLqWK1`)
   tidak ditemukan. Peserta hanya melihat "Question #4" dan gambar, tanpa teks soal maupun opsi.
2. Binding route global `{item_hash}` (`app/Http/VariableBinder.php`) menjalankan lookup yang sama dan
   langsung mengembalikan `abort(404)` *sebelum* controller berjalan. Masalah ini baru terlihat pada uji
   lewat route HTTP nyata di Task 5. Tes regresi Task 2 memanggil controller secara langsung, jadi tidak
   menangkapnya.
3. Back-office menghitung soal dan item dari `exam_item` (isi exam saat ini), sedangkan peserta dan
   scoring memakai snapshot. Ketika item di-detach dari exam ("0 selected"), angka dan isi di admin
   maupun penguji jadi tidak sama dengan yang dikerjakan peserta.

## 2. Perubahan per area

| Area | Commit | Isi |
|---|---|---|
| Fondasi (Task 1) | `9322662` | `App\Services\DeliveryItemSource`: item delivery diambil dari snapshot dulu (by id, bukan `items.hash`), dengan fallback `exam_item`. Regresi `DeliverySnapshotQuestionLoading.php`. |
| Backend peserta (Task 2) | `ffdd364`, merge `ad48038` | `getQuestions` mengembalikan JSON 404 `{error:'question_not_found', message, retryable}` dan menerima `?item_id=`. Regresi `DeliverySnapshotQuestionEndpoint.php`. |
| Frontend peserta (Task 3) | `029fa88`, merge `43201b0` | `Exam/Main.vue`: timeout 20 detik, blok error + tombol **Try again** menggantikan kartu soal kosong, soal nomor sebelumnya tidak ditampilkan selama loading. |
| Admin & penguji (Task 4) | `bbf5096`, `7d46c1f`, merge `98dd027` | Catatan "Exam content frozen at …" di delivery, "(N attached)" + daftar delivery snapshot di QuestionSet, scoring/penguji memakai item snapshot. Regresi `DeliverySnapshotBackOfficeScoring.php`. |
| Temuan E2E (Task 5) | `ee87f7c` | (a) `VariableBinder`: untuk route `exam.get-taker-answer`, `{item_hash}` diteruskan sebagai string mentah sehingga controller me-resolve item dari snapshot. Route `item_hash` lain tidak berubah. Tes endpoint ditambah cek binding router (merah tanpa fix, hijau dengan fix). (b) `Main.vue`: soal yang dikembalikan server dengan gambar atau vignette tapi tanpa teks/opsi (mis. esai berbasis gambar) kini tampil dan bisa dijawab, tidak lagi dianggap error. Placeholder dari props tetap menampilkan error + retry. |

Tidak ada migrasi DB, perubahan `composer.lock`, maupun perubahan `rust-service/`.

## 3. Hasil tes

Lingkungan: PHP 8.1.34, PostgreSQL staging `127.0.0.1:55432/ionbec_staging` (salinan referensi +
fixture QA), `APP_ENV=local`, `USE_RUST_SCORING=false`. Server: `php -S` dengan router Laravel. Browser:
Chromium headless (Playwright 1.63). Semua request lewat route HTTP nyata, termasuk middleware, sesi,
dan tenant per domain (`exam.localhost` → tenant Exam IONBEC, `suksesujian.com` → tenant Sukses Ujian).

**Build frontend:** `NODE_OPTIONS=--max-old-space-size=4096 npx mix --production` sukses (4,3 menit;
setelah fix 3,7 menit, tanpa OOM, sekitar 12 GB RAM tersedia). Aset hasil build tidak di-commit karena
image Docker menjalankan `npm run production` sendiri.

**Regresi** (`APP_ENV=testing php tests/Regression/<file>`, transaksi selalu di-rollback):

```
DeliverySnapshotQuestionLoading.php     PASS 22 checks
DeliverySnapshotQuestionEndpoint.php    PASS 23 checks   (cek router: FAIL tanpa ee87f7c)
DeliverySnapshotBackOfficeScoring.php   PASS 22 checks
```

**E2E browser:**

| Skenario | Hasil |
|---|---|
| Peserta TO, item 936 (TO OTD 230526-04): `GET /exam/questions/x5gLqWK1?item_id=936` | 200, `source=delivery_snapshot` (**sebelum `ee87f7c`: 404 dari binding**) |
| Teks soal, label `N.1`, 5 opsi, 1 gambar termuat, tanpa blok error | lulus |
| Mark As Later tercentang dan tersimpan di `exam-state` | lulus |
| Jawab opsi, `POST /exam/answer` 200, DB `attempt_question` → `question_id` 1277 (item 936), `answer_id` sesuai opsi yang diklik | lulus |
| Simulasi soal tak ada (hash tak dikenal): backend 404 `question_not_found` dan UI menampilkan pesan, kode 404, tombol **Try again**, tanpa kartu kosong; Try again → 200 dan soal tampil | lulus |
| Peserta Onkologi II, item 1960 (vignette + 5 gambar): teks, 5 opsi, 5 gambar, Mark As Later, jawaban tersimpan (`question_id` 3311) | lulus (cek visual "jawaban tetap terpilih setelah reload" sekali gagal karena timing, ulang lulus; DB benar di kedua run) |
| Soal hanya-gambar: tanpa teks + ada opsi | tampil normal |
| Soal hanya-gambar: tanpa teks + tanpa opsi (esai) | **sebelum fix: blok error, kartu disembunyikan**; sesudah `ee87f7c`: kartu 3.1 + editor tampil, tanpa error |
| Penguji (`qa-scorer-exam`): `/back-office/scoring/{delivery}/exam/{attempt}` menampilkan item 936 (sudah di-detach dari exam) dengan teks, gambar, dan jawaban peserta bertanda benar; *Finish & Recalculate* 200 | `attempts.score` = 20.00, `finish_scoring` = true, `attempt_question.is_correct` = true, `score` = 100 |
| Admin (`qa-admin-exam`): delivery/question & delivery/taker | "Exam content frozen at 2026-05-21 16:06 (5 question set(s)) … The exam currently has 4 question set(s), which differs…"; soal item 936 ada di daftar 5 Questions |
| Admin QuestionSet item 936 | "(0 attached)", catatan "Not attached to any exam now, but used by 2 scheduled delivery(ies)…", daftar delivery, dan peringatan detached |

Screenshot ada di `docs/fix-evidence/`. Gambar soal di staging memakai placeholder karena file lampiran
produksi tidak ada di salinan DB.

- `01-TO-OTD-230526-04-soal.png`, `01-TO-OTD-230526-04-error-try-again.png`, `01-TO-OTD-230526-04-after-retry.png`
- `04-Onkologi-II-soal.png`
- `06-image-only-mc.png`, `06-image-only-essay.png`
- `07-penguji-scoring-item-936.png`, `08-penguji-after-finish.png`
- `09-admin-delivery-frozen-note.png`, `10-admin-questionset-936-0-attached.png`

Data uji: DB staging di-dump sebelum uji (`pre-e2e.dump`) dan dipulihkan setelahnya. Attempt dan jawaban
uji sudah hilang, token QA kembali `is_login=false`.

## 4. Belum diuji / risiko

- **Rust scoring.** `rust-service/src/scoring.rs` (`fetch_exam_items`, baris 254–276) masih menghitung
  dari `exam_item`, bukan snapshot. Jika produksi memakai `USE_RUST_SCORING=true` (default kode), item
  yang di-detach seperti 936 **tidak ikut dinilai** dan nilainya bisa berbeda dari path PHP yang diuji di
  sini. Ada dua pilihan sebelum menilai delivery terdampak: set `USE_RUST_SCORING=false`, atau buat task
  lanjutan untuk menyamakan Rust ke snapshot.
- PHP 8.3 tidak diuji. Produksi memakai `php:8.1-fpm`, dan tidak ada sintaks 8.2+ di diff.
- Websocket/broadcast (404 di staging lokal) dan antrean job tidak diuji. Scoring berjalan sinkron.
- File gambar asli tidak diuji, hanya placeholder. Jalur `/attachment/stream/{uuid}` sudah diuji.

## 5. Known limitation & observasi

- Soal yang **dihapus** setelah delivery dijadwalkan tidak tampil read-only di layar penguji. Peserta
  melihat catatan "removed by the administrator", dan scoring melewatinya.
- Exam `is_random=true` (TO, exam 48) mengacak ulang urutan item di **setiap reload** halaman peserta.
  Jawaban tetap aman karena disimpan per hash soal. Ini perilaku lama, tidak diubah.
- Mark As Later tidak tampil tercentang lagi setelah reload halaman, padahal tersimpan di localStorage.
  Logika `laters` identik dengan `main` (perilaku lama), dan di luar cakupan.
- Lingkungan uji: `APP_KEY` di env staging bukan key valid, jadi uji memakai key lokal sementara di `.env`
  worktree (sudah dihapus).

## 6. Langkah deploy (di luar jam ujian)

1. Pastikan tidak ada delivery yang sedang berlangsung. Umumkan jendela maintenance.
2. Pemilik repo mereview, lalu merge `fix/exam-missing-question` → `main`. Catat commit `main` sebelumnya
   untuk rollback.
3. Backup DB produksi (`pg_dump -Fc`) sebelum deploy, meski tidak ada migrasi.
4. Putuskan `USE_RUST_SCORING` (lihat §4). Rekomendasi: `false` sampai Rust memakai snapshot.
5. Coolify: redeploy app. Image menjalankan `npm ci && npm run production`. Setelah container hidup:
   `php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear`.
6. Smoke test dengan token/delivery uji (bukan peserta nyata):
   - Peserta membuka item TO OTD 230526-04. Network `GET /exam/questions/…` = 200, teks, opsi, dan gambar tampil.
   - Delivery admin menampilkan "Exam content frozen at …". QuestionSet item 936 menampilkan "(0 attached)" + daftar delivery.
   - Penguji membuka scoring attempt uji, item tampil, *Finish & Recalculate* menghasilkan nilai.
   - Hapus attempt/token uji setelahnya.
7. **Rollback:** redeploy commit `main` sebelumnya di Coolify, lalu clear cache seperti langkah 5. Tidak
   ada perubahan skema, jadi rollback hanya di kode. Restore DB hanya dibutuhkan jika ada kerusakan data.
