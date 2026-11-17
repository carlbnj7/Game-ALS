# Game ALS

Gamified Assessment for Mastery and Excellence in ALS — DepEd ALS, Division of Bukidnon.

## Install

Game ALS runs on PHP 8.0+ and MySQL 5.7+ / MariaDB 10.3+ (Hostinger shared hosting, XAMPP, or Laragon).

## 1. Upload the files
Upload everything inside the `upload-to-public_html` folder into your site folder
(for Hostinger: `public_html`, or a subfolder like `public_html/gameals`).

## 2. Make the database
1. In hPanel → Databases → MySQL Databases, make a new database and user (on XAMPP/Laragon use phpMyAdmin; user `root`, empty password).
2. Open phpMyAdmin, choose the new database, and import, in this order:
   - `database/01_schema.sql`
   - `database/02_questions_elementary.sql` (Elementary questions)
   - `database/03_questions_jhs.sql` (Junior High School questions)

   **Upgrading from version 1 or 2?** Do not import 01 again. Import only:
   - `database/04_accounts_places.sql` — **once** (adds ALS IDs, PINs, QR passes, municipalities and districts;
     accounts and scores stay)
   - `database/02_questions_elementary.sql` and `database/03_questions_jhs.sql` only if you came from version 1.

## 3. Connect the app to the database
1. In the `config` folder, copy `config.sample.php` and name the copy `config.php`.
2. Open `config.php` and type your database name, user, and password.
3. Keep `'debug' => false` on the live site.

Never share `config.php`.

## 4. First sign-in
- ALS ID: `ADM0001` (or username `admin`)
- Password: `ChangeMe123!`

The app then asks for a new password, your own username, and a 4-digit PIN.

## 5. Set up
1. **Municipalities** — add each municipality, then its districts.
2. **CLCs** — add each Community Learning Center and choose its district.
3. **Teachers** — add teacher (or admin) accounts and tick their CLCs. The app makes the ALS ID (e.g. TCH202600002 / ADM202600003) and a starter password, shown once on screen.
4. **Settings** — type the SDS name for the certificates.
5. Teachers add learners one by one, or upload the AF3 form (.xlsx or .csv). Each learner gets an ALS ID (e.g. ALS202600004) and starter password. Print the sign-in slips and/or the QR passes.

## Accounts and sign-in
- Every account has an ALS ID made by the app. It never changes and always works for signing in.
- On the first sign-in everyone picks their own username, a new password, and a 4-digit PIN.
- **QR passes** (Teachers and Admin → QR passes): print a pass for each person. Scan it, then type the PIN.
  Before the first sign-in, the pass asks for the starter password instead.
  5 wrong PINs lock the pass; the teacher or admin can unlock it or make a new QR (the old pass stops working).
- Forgot the PIN? The teacher (for learners) or admin (for staff) presses **Reset PIN**.

## Game rules built in
- 7 Learning Strands, 3 levels each (Easy, Medium, Difficult), 3 activities per level (items 1–25, 26–50, 51–75).
- Strands go in order: LS1 English → LS1 Filipino → LS2 → LS3 → LS4 → LS5 → LS6. The next strand stays locked until every activity of the strand before it is passed with 75% or more.
- 75% passes an activity. Levels 2 and 3 open once all of Level 1 is passed.
- 3 lives: a wrong answer takes one. 5 correct in a row gives one back (up to 5). No lives left = the activity starts over.
- 3 hints per activity (each takes away two wrong choices); hints do not carry over.
- A Certificate of Passing for each level; a Certificate of Readiness after all 7 strands.
- Leaderboards, learners, reports and QR passes: Admin can filter by municipality, district, and CLC; Teacher for their CLCs; Learner for their grade level.
- Reports: mastered and least mastered per learner, plus a consolidated CSV per CLC and grade level.

## Updating questions
Admin → Questions. Edit the question, choices, correct answer, or explanation, or hide a question.
Elementary learners get the Elementary questions; Junior High learners get the JHS questions.
