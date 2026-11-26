import { useEffect, useRef, useState } from 'react'

export function useAsync(asyncFn, deps = []) {
  const [reloadCount, setReloadCount] = useState(0)
  const [result, setResult] = useState({ key: null })
  const asyncFnRef = useRef(asyncFn)

  const key = `${JSON.stringify(deps)}:${reloadCount}`

  useEffect(() => {
    asyncFnRef.current = asyncFn
  }, [asyncFn])

  useEffect(() => {
    let cancelled = false

    asyncFnRef.current()
      .then((data) => {
        if (!cancelled) setResult({ key, data, error: null })
      })
      .catch((error) => {
        if (!cancelled) setResult({ key, data: null, error })
      })

    return () => {
      cancelled = true
    }
  }, [key])

  const loading = result.key !== key

  return {
    loading,
    data: loading ? null : result.data,
    error: loading ? null : result.error,
    reload: () => setReloadCount((count) => count + 1),
  }
}