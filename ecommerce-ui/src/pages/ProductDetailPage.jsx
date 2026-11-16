import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getProductDetail } from '../api/aggregate'
import { addToCart } from '../api/cartApi'
import AsyncView from '../components/AsyncView'
import ReviewForm from '../components/ReviewForm'
import StarRating from '../components/StarRating'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'

export default function ProductDetailPage({ userId }) {
  const { id } = useParams()
  const { loading, data, error, reload } = useAsync(
    () => getProductDetail(id),
    [id],
  )
  const [quantity, setQuantity] = useState('1')
  const [cartMessage, setCartMessage] = useState(null)
  const [addingToCart, setAddingToCart] = useState(false)

  async function handleAddToCart() {
    const amount = Number(quantity)
    if (!Number.isInteger(amount) || amount < 1) {
      setCartMessage({ type: 'error', text: 'Masukkan jumlah minimal 1.' })
      return
    }

    if (amount > data.product.stock) {
      setCartMessage({
        type: 'error',
        text: `Jumlah melebihi stok yang tersedia (${data.product.stock}).`,
      })
      return
    }

    setAddingToCart(true)
    setCartMessage(null)
    try {
      await addToCart(userId, Number(id), amount)
      setCartMessage({ type: 'success', text: 'Produk masuk ke keranjang.' })
    } catch (err) {
      setCartMessage({ type: 'error', text: describeError(err) })
    } finally {
      setAddingToCart(false)
    }
  }

  return (
    <section className="product-detail">
      <Link className="back-link" to="/">← Kembali ke produk</Link>

      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && (
          <>
            <div className="detail-layout">
              <div className={`product-art detail-art product-art-tone-${Number(id) % 4}`}>
                <span className="product-art-mark" aria-hidden="true">✦</span>
              </div>

              <div className="detail-info">
                <span className="eyebrow">Pilihan Nusa</span>
                <h1>{data.product.name}</h1>
                {data.reviews && (
                  <StarRating
                    average={data.reviews.summary.average_rating}
                    total={data.reviews.summary.total_reviews}
                  />
                )}
                <p className="price">{formatRupiah(data.product.price)}</p>
                <p>{data.product.description || 'Produk pilihan dengan kualitas yang bisa kamu andalkan.'}</p>
                <p className="muted detail-stock">
                  {data.product.stock > 0
                    ? `${data.product.stock} tersedia`
                    : 'Stok sedang habis'}
                </p>

                <div className="actions">
                  <input
                    type="number"
                    min="1"
                    max={data.product.stock}
                    step="1"
                    value={quantity}
                    aria-label="Jumlah"
                    onChange={(event) => {
                      setQuantity(event.target.value)
                      setCartMessage(null)
                    }}
                  />
                  <button
                    type="button"
                    onClick={handleAddToCart}
                    disabled={data.product.stock < 1 || addingToCart}
                  >
                    {addingToCart ? 'Menambahkan...' : 'Tambahkan ke keranjang'}
                  </button>
                </div>

                {cartMessage && (
                  <p className={`alert alert-${cartMessage.type}`} role="status">
                    {cartMessage.text}
                  </p>
                )}
              </div>
            </div>

            <section className="detail-reviews">
              <h2 className="section-heading">Ulasan pelanggan</h2>
              {data.reviews ? (
                <ul className="reviews">
                  {data.reviews.reviews.map((review) => (
                    <li key={review.id}>
                      <StarRating average={review.rating} total={1} compact />
                      <p>{review.comment || <em className="muted">Tanpa komentar</em>}</p>
                      <small className="muted">
                        User {review.user_id},{' '}
                        {new Date(review.created_at).toLocaleDateString('id-ID')}
                      </small>
                    </li>
                  ))}
                  {data.reviews.reviews.length === 0 && (
                    <li className="muted">Belum ada ulasan. Jadilah yang pertama memberi ulasan.</li>
                  )}
                </ul>
              ) : (
                <p className="alert alert-error" role="status">
                  Ulasan tidak dapat dimuat saat ini.
                </p>
              )}

              <ReviewForm productId={Number(id)} userId={userId} onCreated={reload} />
            </section>
          </>
        )}
      </AsyncView>
    </section>
  )
}
