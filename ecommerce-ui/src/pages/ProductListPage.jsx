import { useState } from 'react'
import { getProductsWithRating } from '../api/aggregate'
import AsyncView from '../components/AsyncView'
import ProductCard from '../components/ProductCard'
import { useAsync } from '../hooks/useAsync'

function SearchIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" strokeWidth="1.7" />
      <path d="m16 16 4.2 4.2" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
    </svg>
  )
}

export default function ProductListPage() {
  const { loading, data, error, reload } = useAsync(getProductsWithRating)
  const [query, setQuery] = useState('')
  const filteredProducts = (data ?? []).filter((product) =>
    product.name.toLowerCase().includes(query.trim().toLowerCase()),
  )

  return (
    <section>
      <div className="catalog-hero">
        <div className="catalog-copy">
          <span className="eyebrow">Pilihan untuk keseharian</span>
          <h1>Hal baik dimulai dari pilihan yang tepat.</h1>
          <p>Jelajahi koleksi pilihan dengan kualitas yang terasa, untuk menemani setiap harimu.</p>
        </div>
        <div className="hero-art" aria-hidden="true">
          <span className="hero-art-mark">✦</span>
        </div>
      </div>

      <div className="catalog-toolbar">
        <div>
          <h2>Produk pilihan</h2>
          <p>Temukan sesuatu yang pas untukmu.</p>
        </div>
        <label className="search-field">
          <SearchIcon />
          <input
            type="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Cari produk..."
            aria-label="Cari produk"
          />
        </label>
      </div>

      <AsyncView loading={loading} error={error} onRetry={reload}>
        {filteredProducts.length > 0 ? (
          <div className="grid">
            {filteredProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        ) : (
          <p className="empty-state">
            {data?.length
              ? 'Produk yang kamu cari belum ditemukan.'
              : 'Belum ada produk untuk ditampilkan.'}
          </p>
        )}
      </AsyncView>
    </section>
  )
}
