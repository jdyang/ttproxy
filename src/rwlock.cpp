#include "rwlock.h"

  RWLock::RWLock()
  {
  	pthread_rwlock_init(&rwlock, NULL);
  }

  RWLock::~RWLock()
  {
  	pthread_rwlock_destroy(&rwlock);
  }

  void RWLock::ReadLock()
  {
  	pthread_rwlock_rdlock(&rwlock);
  }

  void RWLock::WriteLock()
  {
  	pthread_rwlock_wrlock(&rwlock);
  }

  void RWLock::Unlock()
  {
  	pthread_rwlock_unlock(&rwlock);
  }
