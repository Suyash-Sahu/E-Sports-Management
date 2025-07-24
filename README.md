# E-Sports Hub

A full-featured PHP web application for managing and participating in e-sports tournaments. The platform supports both players and organizers, offering registration, tournament creation, player dashboards, leaderboards, and more.

## Features

- **User Registration & Login**: Separate flows for players and organizers.
- **Player Dashboard**: View profile, statistics, achievements, match history, joined tournaments, and upcoming matches.
- **Organizer Dashboard**: Create and manage tournaments, select winners, manage participants, and view stats.
- **Tournament Management**: Create tournaments with various levels (Inter-college, State, All India, Custom/Open), formats (Single/Double Elimination, Round Robin, Swiss), and team requirements.
- **Join Tournaments**: Players can join eligible tournaments, with checks for capacity and eligibility.
- **Match Results**: Organizers can submit match results, update player/team stats, and award badges.
- **Leaderboard**: Displays top players by rank points, with win rates and game stats.
- **Badges & Achievements**: Players earn badges for tournament wins at different levels.
- **Responsive UI**: Modern, responsive design using custom CSS and Bootstrap.

## Database Schema

- MySQL database with tables for users, player profiles, organizer profiles, tournaments, matches, player rankings, badges, and more.
- See [`esports.sql`](esports.sql) for the full schema and sample data.

## Setup Instructions

1. **Clone the repository**

   ```bash
   git clone <repo-url>
   cd esports
   ```

2. **Database Setup**
   - Create a MySQL database named `esports`.
   - Import the schema and sample data:
     ```bash
     mysql -u root -p esports < esports.sql
     ```
   - Update `includes/db_connection.php` with your MySQL credentials if needed.

3. **Web Server Setup**
   - Place the project in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Make sure PHP and MySQL are running.
   - Access the app at `http://localhost/esports/`.

4. **Dependencies**
   - Uses Bootstrap (via CDN) and FontAwesome for UI.
   - No Composer or npm dependencies required.

## File Structure

- `index.php` — Landing page with hero, features, and featured tournaments
- `register.php` — Registration for players and organizers
- `login.php` / `logout.php` — Authentication
- `player_dashboard.php` — Player's dashboard
- `organizer_dashboard.php` — Organizer's dashboard
- `tournaments.php` — List and join tournaments
- `create_tournament.php` — Organizer: create new tournament
- `join_tournament.php` — Player: join a tournament
- `match_results.php` — Organizer: submit match results
- `leaderboard.php` — Player rankings
- `includes/` — Shared PHP includes (DB connection, header, footer, functions)
- `css/style.css` — Custom styles
- `esports.sql` — Database schema and sample data

## Usage

- **Players**: Register, join tournaments, play matches, view stats and achievements.
- **Organizers**: Register, create tournaments, manage participants, submit results, and select winners.



**Developed by Suyash Sahu and contributors.**
