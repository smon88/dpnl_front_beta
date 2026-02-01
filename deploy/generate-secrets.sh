#!/bin/bash

# ============================================
# Devil Panels - Secret Generator
# ============================================
# Genera todos los secretos necesarios para produccion
#
# Uso: bash generate-secrets.sh
# ============================================

echo "============================================"
echo "Devil Panels - Generador de Secretos"
echo "============================================"
echo ""
echo "Copia estos valores a tus archivos .env"
echo ""
echo "============================================"
echo ""

# APP_KEY para Laravel (base64)
echo "# Laravel APP_KEY"
echo "APP_KEY=base64:$(openssl rand -base64 32)"
echo ""

# Shared Secret (64 caracteres hex)
echo "# Shared Secret (Laravel + Node)"
echo "SHARED_SECRET=$(openssl rand -hex 32)"
echo ""

# JWT Secret
echo "# JWT Secret (Node)"
echo "JWT_SECRET=$(openssl rand -hex 16)"
echo ""

# Session Secret
echo "# Session Secret (Node)"
echo "SESSION_SECRET=$(openssl rand -hex 16)"
echo ""

# Redis Password
echo "# Redis Password"
echo "REDIS_PASSWORD=$(openssl rand -base64 24 | tr -d '/+=')"
echo ""

# PostgreSQL Password (para el backend Node)
echo "# PostgreSQL Password (para backend Node)"
echo "DB_PASSWORD=$(openssl rand -base64 18 | tr -d '/+=')"
echo ""

# Project Secrets (ejemplo)
echo "# Project Secrets (agregar uno por proyecto)"
echo "# Formato: slug:secret,slug2:secret2"
echo "PROJECT_SECRET_EXAMPLE=$(openssl rand -hex 16)"
echo ""

echo "============================================"
echo "IMPORTANTE: Guarda estos valores de forma segura"
echo "============================================"
