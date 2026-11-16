import { useState } from 'react'
import { BrowserRouter, Link, Route, Routes } from 'react-router-dom'
import Navbar from './components/Navbar'
import CartPage from './pages/CartPage'
import ProductDetailPage from './pages/ProductDetailPage'
import ProductListPage from './pages/ProductListPage'
import './App.css'

function App() {
  const [userId, setUserId] = useState(1)

  return (
    <BrowserRouter>
      <div className="app-shell">
        <div className="announcement">
          Gratis ongkir untuk pesanan pilihan <span>·</span> Belanja nyaman setiap hari
        </div>
        <Navbar userId={userId} onUserChange={setUserId} />
        <main className="page-content">
          <Routes>
            <Route path="/" element={<ProductListPage />} />
            <Route path="/products/:id" element={<ProductDetailPage userId={userId} />} />
            <Route path="/cart" element={<CartPage userId={userId} />} />
          </Routes>
        </main>
        <footer className="site-footer">
          <Link className="brand brand-footer" to="/">nusa<span>.</span></Link>
          <p>Temukan pilihan terbaik untuk kebutuhan sehari-hari.</p>
        </footer>
      </div>
    </BrowserRouter>
  )
}

export default App
