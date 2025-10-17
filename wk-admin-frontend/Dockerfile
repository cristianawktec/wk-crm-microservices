# Build stage
FROM node:20-alpine AS build

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
# Use npm install to allow building when package-lock.json is missing in the repo
RUN npm install --legacy-peer-deps

# Copy source code
COPY . .

# Build application
RUN npm run build:prod

# Production stage
FROM nginx:alpine

# Copy built application
COPY --from=build /app/dist/wk-admin-frontend /usr/share/nginx/html

# Copy nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 4200

# Start nginx
CMD ["nginx", "-g", "daemon off;"]