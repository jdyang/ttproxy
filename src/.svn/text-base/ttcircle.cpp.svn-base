#include "ttcircle.h"
#include "log.h"
#include <algorithm>
int32_t TTCircle::TT_CONNECT_NUM = 1;
TTCircle:: TTCircle()
{
	m_bconsist = true;
}

TTCircle:: ~TTCircle()
{
  for(map<string,TTconPool *>::iterator it = m_pool.begin();it!=m_pool.end();it++ )
  {
  	it->second->UnInit();
  	delete it->second;
  }
}

map<string,TTconPool *> TTCircle:: get_pool()
{
	RLockGuard  rl(&m_rwlock);
	return m_pool;
}

TTconPool * TTCircle:: GetTTPool(string key)
{
	RLockGuard  rl(&m_rwlock);
	string name = m_hnew.get_node(key);
	LOG_DEBUG(" %s %d %s key %s server %s .",__FILE__,__LINE__,__FUNCTION__,key.c_str(),name.c_str());
	map<string,TTconPool *>::iterator it = m_pool.find(name);
	if( m_pool.end() == it )
	{
		return NULL;
	}
	return it->second;
}

TTconPool * TTCircle:: GetTTPoolOld(string key)
{
	RLockGuard  rl(&m_rwlock);
	string name = m_hold.get_node(key);
	LOG_DEBUG(" %s %d %s key %s server %s .",__FILE__,__LINE__,__FUNCTION__,key.c_str(),name.c_str());
	map<string,TTconPool *>::iterator it = m_pool.find(name);
	if( m_pool.end() == it )
	{
		return NULL;
	}
	return it->second;
}

bool TTCircle:: add_server(string name,string ip,int port)
{
	LOG_INFO(" %s %d %s name %s server %s  port %d.",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port);
	WLockGuard wl(&m_rwlock);
  if( m_server.end() != m_server.find(name) )
  {
  	return false;
  }
  m_server.insert(make_pair<string,pair<string,int> >(name,make_pair<string,int>(ip,port)));
  m_hnew.add_node(name);
  TTconPool * ttc = new TTconPool();
  LOG_INFO(" %s %d %s add server success name %s server %s  port %d.",__FILE__,__LINE__,__FUNCTION__,name.c_str(),ip.c_str(),port);
  ttc->Init(TT_CONNECT_NUM,ip,port);
  m_pool.insert(make_pair<string,TTconPool *>(name,ttc));
  m_bconsist = false;
  return true;
}

bool TTCircle:: del_server(string name)
{
	WLockGuard wl(&m_rwlock);
	map<string,TTconPool *>::iterator it= m_pool.find(name);
  if( m_pool.end() == it )
  {
  	return false;
  }
  LOG_INFO(" %s %d %s del server success name %s .",__FILE__,__LINE__,__FUNCTION__,name.c_str());
 // TTconPool * ttc = it->second;
  m_hnew.del_node(name);
 // ttc->UnInit();
 // delete ttc;
 // m_pool.erase(it);
  m_server.erase(name);
  m_bconsist = false;
  return true;
}

bool TTCircle:: minus(list<string>& lname,string& name)
{
	for(map<string,pair<string,int> >::iterator it=m_server.begin();it!=m_server.end();it++)
	{
		if(lname.end() == find(lname.begin(),lname.end(),it->first))
		{
			name = it->first;
			return true;
		}
	}
	return false;
}

bool TTCircle:: exist(string name)
{
	if( m_server.end() == m_server.find(name) )
  {
  	return false;
  }
  return true;
}

void TTCircle:: finish_consist()
{
	WLockGuard wl(&m_rwlock);
	if( m_hnew.serverset.size() < m_hold.serverset.size() )
	{
			list<string> lkey;
			for(set<string>::iterator it = m_hold.serverset.begin();it!=m_hold.serverset.end();it++)
			{
					bool bin = false;
					for(set<string>::iterator ite = m_hnew.serverset.begin();ite!=m_hnew.serverset.end();ite++)
					{
							if( *it == *ite )
							{
									bin = true;
									break;
							}
					}
					if(!bin) lkey.push_back(*it);
			}
			for(list<string>::iterator iter = lkey.begin();iter!=lkey.end();iter++)
			{
					map<string,TTconPool *>::iterator it= m_pool.find(*iter);
				  if( m_pool.end() != it )
				  {
						 	TTconPool * ttc = it->second;
						 	ttc->UnInit();
						 	delete ttc;
						 	m_pool.erase(it);
					}
			}
	}
	m_hold = m_hnew;
	m_bconsist = true;
}
