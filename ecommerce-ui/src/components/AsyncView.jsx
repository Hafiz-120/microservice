export default function AsyncView({ loading, error, onRetry, children }) {
  if (loading) {
    return (
      <div className="async-loading" role="status" aria-live="polite">
        <span className="loading-spinner" aria-hidden="true" />
        Memuat data...
      </div>
    )
  }

  if (error) {
    return (
      <div className="alert alert-error" role="alert">
        <p>{error.message || 'Terjadi kesalahan saat memuat data.'}</p>
        <button type="button" onClick={onRetry}>Coba lagi</button>
      </div>
    )
  }

  return children
}
