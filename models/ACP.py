
import numpy as np
from sklearn.decomposition import PCA
from sklearn.preprocessing import StandardScaler

def ACP(df, n_components=0.95):
    #Normalisation 
    scaler = StandardScaler()
    X = scaler.fit_transform(df) 

    # n_components=0.95 -> conserver 95% de la variance
    pca = PCA(n_components=n_components) 

    data_sortie = pca.fit_transform(X)

    return data_sortie



def ACP_v2(X_train, X_test, n_components=0.95):
    #Normalisation 
    scaler = StandardScaler()
    X = scaler.fit_transform(X_train) 
    X_test = scaler.transform(X_test)

    # n_components=0.95 -> conserver 95% de la variance
    pca = PCA(n_components=n_components) 

    X_train_pca = pca.fit_transform(X)
    X_test_pca = pca.transform(X_test)

    return X_train_pca, X_test_pca