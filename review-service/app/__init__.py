# app/__init__.py
# Pabrik aplikasi (application factory): merakit Flask app, koneksi database, routes,
# dan penanganan error umum dalam satu fungsi create_app().

import logging
import os

from dotenv import load_dotenv
from flask import Flask, jsonify, request

from app.database import init_db
from app.routes.review_routes import review_bp


def create_app():
    # .env dibaca lebih dulu supaya FLASK_DEBUG sudah terbaca saat Flask dibuat.
    load_dotenv()

    app = Flask(__name__)
    app.logger.setLevel(logging.INFO)
    allowed_origins = {
        origin.strip()
        for origin in os.getenv(
            "CORS_ALLOWED_ORIGINS",
            "http://localhost:5173,http://127.0.0.1:5173,http://localhost:4173,http://127.0.0.1:4173",
        ).split(",")
        if origin.strip()
    }

    init_db()
    app.register_blueprint(review_bp)

    @app.after_request
    def add_cors_headers(response):
        origin = request.headers.get("Origin")
        if origin in allowed_origins:
            response.headers["Access-Control-Allow-Origin"] = origin
            response.headers["Access-Control-Allow-Methods"] = "GET, POST, PUT, DELETE, OPTIONS"
            response.headers["Access-Control-Allow-Headers"] = "Content-Type, Authorization"
            response.headers["Access-Control-Max-Age"] = "600"
            response.vary.add("Origin")
        return response

    # Endpoint cek hidup, dipakai juga untuk healthcheck nanti.
    @app.get("/health")
    def health():
        return jsonify({"status": "ok", "service": "review-service"})

    # Flask membalas 404 dan 405 dengan halaman HTML. Kita ubah jadi JSON seperti service lain.
    @app.errorhandler(404)
    def not_found(_error):
        return jsonify({"message": "Endpoint tidak ditemukan"}), 404

    @app.errorhandler(405)
    def method_not_allowed(_error):
        return jsonify({"message": "Method tidak diizinkan untuk endpoint ini"}), 405

    return app
