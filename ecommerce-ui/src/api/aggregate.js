import { request } from './http'
import { getCart } from './cartApi'
import { getProduct } from './productApi'
import { getProductReviews } from './reviewApi'

// Daftar produk beserta ringkasan rating.
// Penggabungan sekarang dilakukan gateway: browser cukup memanggil /catalog satu kali.
export async function getProductsWithRating() {
  const response = await request(import.meta.env.VITE_PRODUCT_URL, '/catalog')
  return response.data
}

// Detail produk beserta review. Produk wajib tersedia, review boleh gagal.
export async function getProductDetail(productId) {
  const [productResult, reviewResult] = await Promise.allSettled([
    getProduct(productId),
    getProductReviews(productId),
  ])

  if (productResult.status === 'rejected') throw productResult.reason

  return {
    product: productResult.value,
    reviews: reviewResult.status === 'fulfilled' ? reviewResult.value : null,
  }
}

// Isi keranjang beserta nama dan harga produk.
export async function getCartDetail(userId) {
  const cart = await getCart(userId)

  const items = await Promise.all(
    cart.items.map(async (item) => {
      try {
        const product = await getProduct(item.product_id)
        return { ...item, product }
      } catch {
        return { ...item, product: null }
      }
    }),
  )

  const totalPrice = items.reduce(
    (sum, item) =>
      sum + (item.product ? Number(item.product.price) * item.quantity : 0),
    0,
  )

  return { items, totalPrice }
}
