FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy source code
COPY . .

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S products -u 1001

# Change ownership
RUN chown -R products:nodejs /app
USER products

# Expose port
EXPOSE 3001

# Ensure curl is available for healthcheck
USER root
RUN apk add --no-cache curl ca-certificates || true
USER products

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:3001/health || exit 1

# Start application
CMD ["npm", "start"]