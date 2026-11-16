import { useState } from 'react'
import { getCartDetail } from '../api/aggregate'
import { clearCart, removeCartItem, updateCartItem } from '../api/cartApi'
import AsyncView from '../components/AsyncView'
import { useAsync } from '../hooks/useAsync'
import { describeError, formatRupiah } from '../utils/format'

export default function CartPage({ userId }) {
  const { loading, data, error, reload } = useAsync(
    () => getCartDetail(userId),
    [userId],
  )
  const [actionError, setActionError] = useState(null)
  const [pendingAction, setPendingAction] = useState(false)

  async function run(action) {
    setActionError(null)
    setPendingAction(true)
    try {
      await action()
      reload()
    } catch (err) {
      setActionError(err)
    } finally {
      setPendingAction(false)
    }
  }

  return (
    <section>
      <h1 className="cart-heading">
        Keranjang <span>· User {userId}</span>
      </h1>

      {actionError && (
        <p className="alert alert-error" role="alert">{describeError(actionError)}</p>
      )}

      <AsyncView loading={loading} error={error} onRetry={reload}>
        {data && data.items.length === 0 && (
          <div className="empty-state">
            Keranjangmu masih kosong. Yuk, temukan produk yang kamu suka.
          </div>
        )}

        {data && data.items.length > 0 && (
          <>
            <div className="cart-table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th aria-label="Aksi" />
                  </tr>
                </thead>
                <tbody>
                  {data.items.map((item) => (
                    <tr key={item.id}>
                      <td>{item.product ? item.product.name : <em>Produk tidak tersedia</em>}</td>
                      <td>{item.product ? formatRupiah(item.product.price) : '-'}</td>
                      <td>
                        <span className="quantity-controls">
                          <button
                            type="button"
                            aria-label="Kurangi jumlah"
                            disabled={pendingAction || item.quantity <= 1}
                            onClick={() => run(() => updateCartItem(item.id, item.quantity - 1))}
                          >
                            −
                          </button>
                          <span className="qty">{item.quantity}</span>
                          <button
                            type="button"
                            aria-label="Tambah jumlah"
                            disabled={
                              pendingAction
                              || !item.product
                              || item.quantity >= item.product.stock
                            }
                            onClick={() => run(() => updateCartItem(item.id, item.quantity + 1))}
                          >
                            +
                          </button>
                        </span>
                      </td>
                      <td>
                        {item.product
                          ? formatRupiah(Number(item.product.price) * item.quantity)
                          : '-'}
                      </td>
                      <td>
                        <button
                          className="button-secondary"
                          type="button"
                          disabled={pendingAction}
                          onClick={() => run(() => removeCartItem(item.id))}
                        >
                          Hapus
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="cart-summary">
              <div>
                <p>Total belanja</p>
                <strong>{formatRupiah(data.totalPrice)}</strong>
              </div>
              <button
                className="button-secondary"
                type="button"
                disabled={pendingAction}
                onClick={() => run(() => clearCart(userId))}
              >
                {pendingAction ? 'Memproses...' : 'Kosongkan keranjang'}
              </button>
            </div>
          </>
        )}
      </AsyncView>
    </section>
  )
}
