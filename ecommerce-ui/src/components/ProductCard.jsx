import { Link } from 'react-router-dom'
import { formatRupiah } from '../utils/format'
import StarRating from './StarRating'

export default function ProductCard({ product }) {
  const { id, name, price, stock, rating } = product
  const tone = Number(id) % 4

  return (
    <article className="card">
      <Link
        className={`product-art product-art-tone-${tone}`}
        to={`/products/${id}`}
        aria-label={`Lihat ${name}`}
      >
        <span className="stock-badge">
          {stock > 0 ? `${stock} tersedia` : 'Stok habis'}
        </span>
        <span className="product-art-mark" aria-hidden="true">✦</span>
      </Link>
      <div className="card-body">
        <h3><Link to={`/products/${id}`}>{name}</Link></h3>
        <StarRating
          average={rating?.average_rating}
          total={rating?.total_reviews}
        />
        <p className="price">{formatRupiah(price)}</p>
      </div>
    </article>
  )
}
