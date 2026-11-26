import express from "express";
import cors from "cors";
import { createProxyMiddleware } from "http-proxy-middleware";

const PORT = process.env.PORT ?? 8080;
const PRODUCT_URL = process.env.PRODUCT_URL ?? "http://localhost:3000";
const CART_URL = process.env.CART_URL ?? "http://localhost:3001";
const REVIEW_URL = process.env.REVIEW_URL ?? "http://localhost:3002";
const ORDER_URL = process.env.ORDER_URL ?? "http://localhost:3003";

const app = express();
app.use(cors());

// Aggregation: produk beserta rating dalam satu response
app.get("/catalog", async (req, res) => {
  try {
    const productResponse = await fetch(`${PRODUCT_URL}/products`);
    if (!productResponse.ok) {
      return res
        .status(502)
        .json({ message: "Product service mengembalikan error" });
    }
    const { data: products } = await productResponse.json();
    const catalog = await Promise.all(
      products.map(async (product) => {
        try {
          const reviewResponse = await fetch(
            `${REVIEW_URL}/reviews/product/${product.id}`,
          );
          if (!reviewResponse.ok) throw new Error("review-service error");
          const { data } = await reviewResponse.json();
          return { ...product, rating: data.summary };
        } catch {
          // review-service bermasalah: produk tetap ditampilkan, hanya rating-nya kosong
          return { ...product, rating: null };
        }
      }),
    );
    res.json({ message: "Berhasil mengambil katalog", data: catalog });
  } catch {
    res
      .status(503)
      .json({
        message: "Product service tidak dapat dihubungi, coba lagi nanti",
      });
  }
});

const routes = {
  "/products": PRODUCT_URL,
  "/carts": CART_URL,
  "/reviews": REVIEW_URL,
  "/orders": ORDER_URL,
};

for (const [path, target] of Object.entries(routes)) {
  app.use(
    createProxyMiddleware({ target, changeOrigin: true, pathFilter: path }),
  );
}

app.listen(PORT, (error) => {
  if (error) {
    console.error(`Gateway gagal berjalan: ${error.message}`);
    process.exit(1);
  }
  console.log(`Gateway di http://localhost:${PORT}`);
});