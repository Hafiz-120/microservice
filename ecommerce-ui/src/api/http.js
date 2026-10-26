
const TIMEOUT_MS = 5000;

export class ApiError extends Error {
    constructor(message, status = 0, errors = null) {
        super(message);
        this.name = "ApiError";
        this.status = status;
        this.errors = errors;
    }
}

export async function request(baseUrl, path, method = "GET", body = null) {
    const controller = new AbortController();

    const timer = setTimeout(() => controller.abort(), TIMEOUT_MS);

    try {
        const response = await fetch(`${baseUrl}${path}`, {
            method,
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            body: body === null ? null : JSON.stringify(body),
            signal: controller.signal,
        });

        const data = await response.json().catch(() => null);

        if (!response.ok) {
            throw new ApiError(
                data?.message || "Terjadi kesalahan pada API",
                response.status,
                data?.errors || null
            );
        }

        return data;
    } catch (error) {
        if (error instanceof ApiError) {
            throw error;
        }

        if (error.name === "AbortError") {
            throw new ApiError("Request timeout setelah 5 detik");
        }

        throw new ApiError(
            error.message || "Gagal menghubungi server"
        );
    } finally {
        clearTimeout(timer);
    }
}
