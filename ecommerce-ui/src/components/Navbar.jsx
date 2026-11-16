import { NavLink } from 'react-router-dom'

const USERS = [1, 2, 3, 4, 5]

export default function Navbar({ userId, onUserChange }) {
  return (
    <header className="navbar">
      <div className="nav-main">
        <NavLink className="brand" to="/" aria-label="Nusa, ke halaman produk">
          nusa<span>.</span>
        </NavLink>

        <nav className="nav-links" aria-label="Navigasi utama">
          <NavLink to="/" end>Produk</NavLink>
          <NavLink to="/cart">Keranjang</NavLink>
        </nav>
      </div>

      <div className="nav-tools">
        <label className="user">
          <span>Belanja sebagai</span>
          <select
            aria-label="Pilih pengguna"
            value={userId}
            onChange={(event) => onUserChange(Number(event.target.value))}
          >
            {USERS.map((id) => (
              <option key={id} value={id}>User {id}</option>
            ))}
          </select>
        </label>
      </div>
    </header>
  )
}
