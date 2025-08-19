# CareerTrack Kubernetes Deployment

This directory contains all the necessary Kubernetes manifests and deployment scripts to deploy the CareerTrack Laravel application on Google Kubernetes Engine (GKE).

## Prerequisites

Before deploying, ensure you have the following installed and configured:

- [Google Cloud SDK (gcloud)](https://cloud.google.com/sdk/docs/install)
- [kubectl](https://kubernetes.io/docs/tasks/tools/)
- [Docker](https://docs.docker.com/get-docker/)
- A Google Cloud Project with billing enabled
- Domain name (for SSL certificate)

## Architecture

The deployment consists of the following components:

- **Laravel Application**: Main application with 3 replicas for high availability
- **MySQL Database**: Persistent database for application data
- **Redis**: Caching and session storage
- **Ingress Controller**: Google Cloud Load Balancer with SSL termination
- **Horizontal Pod Autoscaler**: Automatic scaling based on CPU and memory usage
- **Persistent Storage**: PVCs for database and application storage

## Configuration

### 1. Update Configuration Files

Before deploying, update the following files with your specific values:

#### `k8s/configmap.yaml`
- Update `APP_URL` with your domain
- Modify database, Redis, and mail settings as needed

#### `k8s/secret.yaml`
- Generate base64 encoded values for sensitive data:
  ```bash
  echo -n "your-app-key" | base64
  echo -n "your-db-password" | base64
  echo -n "your-email@gmail.com" | base64
  echo -n "your-app-password" | base64
  ```

#### `k8s/ingress.yaml`
- Replace `careertrack.yourdomain.com` with your actual domain
- Update `kubernetes.io/ingress.global-static-ip-name` if you have a reserved IP

#### `k8s/kustomization.yaml`
- Update `gcr.io/YOUR_PROJECT_ID/careertrack` with your actual GCR registry path

#### `k8s/deploy.sh`
- Update `PROJECT_ID` with your GCP project ID
- Modify `CLUSTER_NAME`, `REGION`, and `ZONE` as needed

### 2. Generate Laravel App Key

Generate a new Laravel application key:

```bash
php artisan key:generate
```

Copy the generated key and update the `APP_KEY` in `k8s/secret.yaml`.

## Deployment

### Option 1: Automated Deployment (Recommended)

1. Make the deployment script executable:
   ```bash
   chmod +x k8s/deploy.sh
   ```

2. Run the deployment script:
   ```bash
   ./k8s/deploy.sh
   ```

The script will:
- Authenticate with Google Cloud
- Create a GKE cluster (if it doesn't exist)
- Build and push the Docker image
- Deploy all Kubernetes resources
- Wait for deployments to be ready
- Run database migrations
- Display the external IP address

### Option 2: Manual Deployment

1. **Create GKE Cluster**:
   ```bash
   gcloud container clusters create careertrack-cluster \
     --zone=us-central1-a \
     --num-nodes=3 \
     --machine-type=e2-standard-2 \
     --enable-autoscaling \
     --min-nodes=1 \
     --max-nodes=10
   ```

2. **Get Cluster Credentials**:
   ```bash
   gcloud container clusters get-credentials careertrack-cluster --zone=us-central1-a
   ```

3. **Build and Push Docker Image**:
   ```bash
   docker build -t gcr.io/YOUR_PROJECT_ID/careertrack:latest .
   docker push gcr.io/YOUR_PROJECT_ID/careertrack:latest
   ```

4. **Deploy Kubernetes Resources**:
   ```bash
   kubectl apply -k k8s/
   ```

5. **Wait for Deployments**:
   ```bash
   kubectl wait --for=condition=available --timeout=600s deployment/careertrack-app -n careertrack
   ```

## Post-Deployment

### 1. Verify Deployment

Check the status of all resources:

```bash
kubectl get all -n careertrack
kubectl get ingress -n careertrack
kubectl get pvc -n careertrack
```

### 2. Run Database Migrations

If migrations didn't run automatically:

```bash
kubectl create job --from=cronjob/careertrack-migration careertrack-migration-manual -n careertrack
```

### 3. Check Application Logs

```bash
kubectl logs -f deployment/careertrack-app -n careertrack
```

### 4. Access Your Application

The application will be available at the external IP assigned by the Load Balancer. You can find it with:

```bash
kubectl get ingress careertrack-ingress -n careertrack
```

## Scaling

The application automatically scales based on CPU and memory usage:

- **CPU**: Scales up when average utilization exceeds 70%
- **Memory**: Scales up when average utilization exceeds 80%
- **Replicas**: Minimum 3, maximum 10

Manual scaling:

```bash
kubectl scale deployment careertrack-app --replicas=5 -n careertrack
```

## Monitoring

### 1. Resource Usage

```bash
kubectl top pods -n careertrack
kubectl top nodes
```

### 2. HPA Status

```bash
kubectl get hpa -n careertrack
kubectl describe hpa careertrack-app-hpa -n careertrack
```

## Troubleshooting

### Common Issues

1. **Pods in Pending State**:
   - Check resource requests/limits
   - Verify PVCs are bound
   - Check node capacity

2. **Database Connection Issues**:
   - Verify MySQL pod is running
   - Check database credentials in secrets
   - Ensure network policies allow communication

3. **SSL Certificate Issues**:
   - Verify domain points to Load Balancer IP
   - Check managed certificate status
   - Wait for certificate provisioning (can take 10-15 minutes)

### Debug Commands

```bash
# Check pod events
kubectl describe pod <pod-name> -n careertrack

# Check pod logs
kubectl logs <pod-name> -n careertrack

# Check service endpoints
kubectl get endpoints -n careertrack

# Check ingress status
kubectl describe ingress careertrack-ingress -n careertrack
```

## Cleanup

To remove the deployment:

```bash
kubectl delete -k k8s/
gcloud container clusters delete careertrack-cluster --zone=us-central1-a
```

## Security Considerations

- All sensitive data is stored in Kubernetes secrets
- SSL termination is handled by Google Cloud Load Balancer
- Network policies can be added for additional security
- Consider using Workload Identity for GCP service authentication
- Regularly rotate secrets and update images

## Cost Optimization

- Use preemptible nodes for development
- Set appropriate resource limits
- Monitor and adjust HPA settings
- Use committed use discounts for production workloads
- Consider regional clusters for better availability

## Support

For issues related to:
- **Kubernetes**: Check the [Kubernetes documentation](https://kubernetes.io/docs/)
- **Google Cloud**: Refer to [GKE documentation](https://cloud.google.com/kubernetes-engine/docs/)
- **Laravel**: Check the [Laravel documentation](https://laravel.com/docs/)
