# Ecommerce UI and services

## Run with Docker Compose

From the repository root, start the complete application:

```powershell
docker compose up --build -d
```

Open the UI at <http://localhost:5173>. The API services are published on
ports 3000 (products), 3001 (cart), 3002 (reviews), and 3003 (orders).
Compose waits for each service health check before starting its dependents.

To inspect startup or service errors:

```powershell
docker compose ps
docker compose logs -f ecommerce-ui cart-service product-service review-service order-service
```

Stop the application without removing its database data:

```powershell
docker compose down
```

## Run the UI outside Docker

Start the backend services from the repository root with `docker compose up -d`,
then run these commands in `ecommerce-ui`:

```powershell
npm ci
npm run dev
```

The UI uses the `VITE_PRODUCT_URL`, `VITE_CART_URL`, and `VITE_REVIEW_URL`
values from `.env`. These URLs must be reachable by the browser.
