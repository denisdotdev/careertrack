#!/bin/bash

# CareerTrack Kubernetes Deployment Script
# This script deploys the CareerTrack application to Google Kubernetes Engine

set -e

# Configuration
PROJECT_ID="your-gcp-project-id"  # Replace with your GCP project ID
CLUSTER_NAME="careertrack-cluster"  # Replace with your cluster name
REGION="us-central1"  # Replace with your preferred region
ZONE="us-central1-a"  # Replace with your preferred zone
NAMESPACE="careertrack"
IMAGE_NAME="gcr.io/${PROJECT_ID}/careertrack"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Starting CareerTrack Kubernetes Deployment${NC}"

# Check if gcloud is installed
if ! command -v gcloud &> /dev/null; then
    echo -e "${RED}❌ gcloud CLI is not installed. Please install it first.${NC}"
    exit 1
fi

# Check if kubectl is installed
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl is not installed. Please install it first.${NC}"
    exit 1
fi

# Authenticate with Google Cloud
echo -e "${YELLOW}🔐 Authenticating with Google Cloud...${NC}"
gcloud auth login --no-launch-browser

# Set the project
echo -e "${YELLOW}📁 Setting GCP project to ${PROJECT_ID}...${NC}"
gcloud config set project ${PROJECT_ID}

# Enable required APIs
echo -e "${YELLOW}🔌 Enabling required GCP APIs...${NC}"
gcloud services enable container.googleapis.com
gcloud services enable compute.googleapis.com
gcloud services enable dns.googleapis.com

# Check if cluster exists, create if not
if ! gcloud container clusters describe ${CLUSTER_NAME} --zone=${ZONE} &> /dev/null; then
    echo -e "${YELLOW}🏗️  Creating GKE cluster ${CLUSTER_NAME}...${NC}"
    gcloud container clusters create ${CLUSTER_NAME} \
        --zone=${ZONE} \
        --num-nodes=3 \
        --machine-type=e2-standard-2 \
        --enable-autoscaling \
        --min-nodes=1 \
        --max-nodes=10 \
        --enable-autorepair \
        --enable-autoupgrade \
        --enable-ip-alias \
        --create-subnetwork=name=careertrack-subnet \
        --network=default \
        --addons=HttpLoadBalancing,HorizontalPodAutoscaling
else
    echo -e "${GREEN}✅ Cluster ${CLUSTER_NAME} already exists${NC}"
fi

# Get cluster credentials
echo -e "${YELLOW}🔑 Getting cluster credentials...${NC}"
gcloud container clusters get-credentials ${CLUSTER_NAME} --zone=${ZONE}

# Build and push Docker image
echo -e "${YELLOW}🐳 Building and pushing Docker image...${NC}"
docker build -t ${IMAGE_NAME}:latest .
docker push ${IMAGE_NAME}:latest

# Create namespace if it doesn't exist
echo -e "${YELLOW}📦 Creating namespace ${NAMESPACE}...${NC}"
kubectl create namespace ${NAMESPACE} --dry-run=client -o yaml | kubectl apply -f -

# Apply Kubernetes manifests
echo -e "${YELLOW}📋 Applying Kubernetes manifests...${NC}"
kubectl apply -k k8s/

# Wait for deployments to be ready
echo -e "${YELLOW}⏳ Waiting for deployments to be ready...${NC}"
kubectl wait --for=condition=available --timeout=600s deployment/careertrack-app -n ${NAMESPACE}
kubectl wait --for=condition=available --timeout=600s deployment/careertrack-mysql -n ${NAMESPACE}
kubectl wait --for=condition=available --timeout=600s deployment/careertrack-redis -n ${NAMESPACE}

# Run database migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
kubectl create job --from=cronjob/careertrack-migration careertrack-migration-manual -n ${NAMESPACE} || true

# Get the external IP
echo -e "${YELLOW}🌐 Getting external IP address...${NC}"
EXTERNAL_IP=$(kubectl get ingress careertrack-ingress -n ${NAMESPACE} -o jsonpath='{.status.loadBalancer.ingress[0].ip}')

if [ -n "$EXTERNAL_IP" ]; then
    echo -e "${GREEN}✅ Deployment successful!${NC}"
    echo -e "${GREEN}🌍 Your application is available at: http://${EXTERNAL_IP}${NC}"
    echo -e "${YELLOW}📝 Note: It may take a few minutes for the SSL certificate to be provisioned${NC}"
else
    echo -e "${YELLOW}⏳ External IP not yet assigned. Please wait a few minutes and check again:${NC}"
    echo -e "${YELLOW}kubectl get ingress careertrack-ingress -n ${NAMESPACE}${NC}"
fi

# Show cluster status
echo -e "${YELLOW}📊 Cluster status:${NC}"
kubectl get pods -n ${NAMESPACE}
kubectl get services -n ${NAMESPACE}
kubectl get ingress -n ${NAMESPACE}

echo -e "${GREEN}🎉 Deployment completed!${NC}"
